import sys
import os

sys.path.append(os.path.join(
    os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'lib'))

import simplejson as json


if len(sys.argv) != 2:
    #sys.exit()
    info_path = '/var/www/html/tmp/resulttop'
else:
    info_path = sys.argv[1]

logfile = open('/tmp/toplog', 'w')
old_stdout = sys.stdout
sys.stdout = logfile

f = open(info_path, 'r')
raw_info = json.load(f)
f.close()


class Node:
    def __init__(self, ip):
        self.ip = ip
        self.interfaces = set()
        self.afts = set()
        self.eafts = set()
        self.arp_afts = set()

    def __repr__(self):
        return '{N %s}' % self.ip

    def __eq__(self, other):
        return self.ip == other.ip

    def __ne__(self, other):
        return not self.__eq__(other)

    def __hash__(self):
        return hash(self.ip)

    @property
    def is_terminal(self):
        return len(self.interfaces) == 1

    @property
    def aafts(self):
        return self.afts | self.eafts

    @property
    def ainterfaces(self):
        return self.interfaces | self.einterfaces


class Interface:
    def __init__(self, node, index):
        self.index = index
        self.node = node
        self.vlanids = set()

    def __repr__(self):
        return '[I %s]' % self.index

    def __eq__(self, other):
        return self.index == other.index

    def __ne__(self, other):
        return not self.__eq__(other)

    def __hash__(self):
        return hash(self.index)


class VlanNode(Node):
    def __init__(self, node, vlan):
        self.vlan = vlan
        self.node = node
        self.ip = node.ip

    def __repr__(self):
        return '{VN %s}' % self.ip

    @property
    def interfaces(self):
        return set(VlanInterface(self, interface)
                   for interface in self.node.interfaces
                   if self.vlan.id in interface.vlanids)

    @property
    def einterfaces(self):
        return set(VlanInterface(self, interface)
                   for interface in self.node.einterfaces
                   if self.vlan.id in interface.vlanids)

    @property
    def afts(self):
        return set((VlanInterface(self, interface),
                    self.vlan.vlan_nodes[other_node.ip])
                   for interface, other_node, vlan
                   in self.node.afts
                   if (vlan == self.vlan and
                       other_node.ip in self.vlan.vlan_nodes))

    @property
    def eafts(self):
        return set((VlanInterface(self, interface),
                    VlanNode(other_node, vlan))
                   for interface, other_node, vlan
                   in self.node.eafts
                   if vlan == self.vlan)

    def aft(self, i):
        return set(e[1] for e in self.afts if e[0] == i)

    def eaft(self, i):
        return set(e[1] for e in self.eafts if e[0] == i)

    def aaft(self, i):
        return self.aft(i) | self.eaft(i)

    def caft(self, i):
        return set(e[1] for e in self.afts if e[0] != i) | set([self])

    def remove_node_from_afts(self, vlan_node):
        for e in self.nodes.afts.copy():
            if e[2] == self.vlanid and e[1] == vlan_node:
                self.nodes.afts.remove(e)

    def remove_interface_from_afts(self, interface):
        for e in self.nodes.afts.copy():
            if e[2] == self.vlanid and e[0] == interface:
                self.nodes.afts.remove(e)


class VlanInterface(Interface):
    def __init__(self, vlan_node, interface):
        self.interface = interface
        self.index = interface.index
        self.node = vlan_node
        self.vlan = vlan_node.vlan

    def __repr__(self):
        return '[VI %s]' % self.index


class Component(VlanNode):
    def __init__(self, first_vlan_node):
        self.vlan_nodes = set([first_vlan_node])
        self.vlan = first_vlan_node.vlan
        self.vlanid = first_vlan_node.vlan.id
        self.ip = first_vlan_node.ip
        #self.interfaces = set()

    def __repr__(self):
        return '{C %s}' % self.ip

    def __eq__(self, other):
        return self.ip == other.ip

    def __ne__(self, other):
        return not self.__eq__(other)

    @property
    def afts(self):
        return reduce(lambda s, vlan_node:
                      s | set((ComponentInterface(self, vlan_interface),
                               self.vlan.vlan_node_component_map[
                                   other_vlan_node.ip])
                              for vlan_interface, other_vlan_node
                              in vlan_node.afts),
                      self.vlan_nodes, set())

    @property
    def eafts(self):
        return reduce(lambda s, vlan_node:
                      s | set((ComponentInterface(self, vlan_interface),
                               Component(other_vlan_node))
                              for vlan_interface, other_vlan_node
                              in vlan_node.eafts),
                      self.vlan_nodes, set())

    @property
    def interfaces(self):
        return reduce(lambda s, vlan_node:
                      s | set(ComponentInterface(self, interface)
                              for interface in vlan_node.interfaces),
                      self.vlan_nodes, set())

    @property
    def einterfaces(self):
        return reduce(lambda s, vlan_node:
                      s | set(ComponentInterface(self, interface)
                              for interface in vlan_node.einterfaces),
                      self.vlan_nodes, set())


class ComponentInterface(VlanInterface):
    def __init__(self, component, vlan_interface):
        self.node = component
        self.interface = vlan_interface
        self.index = vlan_interface.index
        self.vlan = vlan_interface.vlan
        self._id = '%s-%s' % (self.interface.node.ip, self.index)

    def __repr__(self):
        return '[CI %s]' % self._id

    def __eq__(self, other):
        return self._id == other._id

    def __ne__(self, other):
        return not self.__eq__(other)

    def __hash__(self):
        return hash(self._id)


class Vlan:
    def __init__(self, id):
        self.id = id
        self.vlan_nodes = {}
        self.components = set()

        self.vlan_node_component_map = {}
        self.discovered_interfaces = set()
        self.connections = set()
        self.pcg = set()
        self.pdcg = set()

    def __repr__(self):
        return '(V %s)' % self.id

    def __eq__(self, other):
        return self.id == other.id

    def __ne__(self, other):
        return not self.__eq__(other)

    def __hash__(self):
        return int(self.id)


def initialize(raw_info):
    # read file
    infos = {}
    for ip, data in raw_info.iteritems():
        infos[ip] = {
            'aft': data['aft'],
            'mac': data['mac'],
            #'arp': data[2],
            #'ips': data[3],
            'arp_macs': data['arp'],
            'interfaces': data['if'],
        }
    count = 0
    vcount = 0
    for data in raw_info.itervalues():
        for e in data['aft']:
            count += 1
            if e[2] != u'0':
                vcount += 1
    print count
    print vcount
    # build ip-node relations
    #ip_node = {}
    #for ip, node in infos.iteritems():
        #ip_node.update(dict((v, ip) for v in node['ips']))

    # build mac-ip relations
    mac_ip = {}
    for ip, node in infos.iteritems():
        mac_ip.update(dict((v, ip) for v in node['mac']))
        #if node['arp']:
            ##mac_ip.update(dict((v, k)
                               ##for k, v in node['arp'].iteritems()
                               ##if k in infos))
            #mac_ip.update(dict((v, ip_node[k])
                               #for k, v in node['arp'].iteritems()
                               #if k in ip_node))

    # merge aft with arp_macs
    #for ip, info in infos.iteritems():
        #for a in info['arp_macs']:
            #finded = False
            #for b in info['aft']:
                #if a[0] == b[0] and a[1] == b[1]:
                    #finded = True
                    #break
            #if not finded:
                #info['aft'].append(a)

    # initialize nodes & interfaces
    nodes = {}
    admin_domain = infos.keys()
    for ip, info in infos.iteritems():
        node = Node(ip)
        nodes[ip] = node

        temp_dict = {}
        temp_dict_e = {}
        for index, mac, vlanid in info['aft']:
            if mac in mac_ip:
                if mac_ip[mac] in admin_domain:
                    if index not in temp_dict:
                        temp_dict[index] = Interface(node, index)
                    temp_dict[index].vlanids.add(vlanid)
            else:
                if index not in temp_dict_e:
                    temp_dict_e[index] = Interface(node, index)
                temp_dict_e[index].vlanids.add(vlanid)

        node.interfaces = set(temp_dict.values())
        node.einterfaces = set(temp_dict_e.values())

    # initialize vlans
    vlans = {}
    for ip, info in infos.iteritems():
        for index, mac, vlanid in info['aft']:
            if vlanid not in vlans:
                vlans[vlanid] = Vlan(vlanid)
            vlan = vlans[vlanid]

            # vlan nodes & components
            vlan_node = VlanNode(nodes[ip], vlan)
            if vlan_node.ip not in vlan.vlan_nodes:
                component = Component(vlan_node)

                vlan.vlan_nodes[vlan_node.ip] = vlan_node
                #vlan_node.interfaces = set(VlanInterface(vlan_node, interface)
                                           #for interface
                                           #in nodes[vlan_node.ip].interfaces
                                           #if vlanid in interface.vlanids)

                vlan.components.add(component)
                vlan.vlan_node_component_map[vlan_node.ip] = component
            #if mac in mac_ip and mac_ip[mac] in admin_domain:
                #vlan_node = VlanNode(nodes[mac_ip[mac]], vlan)
                #if vlan_node.ip not in vlan.vlan_nodes:
                    #component = Component(vlan_node)

                    #vlan.vlan_nodes[vlan_node.ip] = vlan_node
                    #vlan_node.interfaces = set(
                        #VlanInterface(vlan_node, interface)
                        #for interface
                        #in nodes[vlan_node.ip].interfaces
                        #if vlanid in interface.vlanids)

                    #vlan.components.add(component)
                    #vlan.vlan_node_component_map[vlan_node.ip] = component

    # initialize nodes' afts
    for node in nodes.itervalues():
        info = infos[node.ip]
        node.afts = set((Interface(node, index),
                         nodes[mac_ip[mac]], vlans[vlanid])
                        for index, mac, vlanid in info['aft']
                        if (mac in mac_ip
                            and mac_ip[mac] in admin_domain
                            and mac_ip[mac] != node.ip))
        node.eafts = set((Interface(node, index),
            Node(mac), vlans[vlanid])
            for index, mac, vlanid in info['aft']
            if mac not in mac_ip)
        node.arp_afts = set((Interface(node, index), nodes[mac_ip[mac]])
                            for index, mac in info['arp_macs']
                            if (mac in mac_ip
                                and mac_ip[mac] in admin_domain
                                and mac_ip[mac] != node.ip))

    return nodes, vlans


def reduction_process(components):
    result = None
    for t in components:
        if t.is_terminal:
            count = 0
            for n in components:
                if t == n:
                    continue
                for interface, node in n.afts:
                    if t == node:
                        if len(n.aft(interface)) == 1:
                            count += 1
                            if count > 1:
                                break
                            result = (list(t.interfaces)[0], interface)
                if count > 1:
                    break
            if count == 1:
                link = (result[0].interface.node.node,
                        result[0].interface.interface,
                        result[1].interface.node.node,
                        result[1].interface.interface)
                if link not in invalid_links:
                    return link

    return None


def check_link(link):
    a, ai, b, bj = link

    # exist node between a and b ?
    for vlan in vlans.itervalues():
        if a.ip not in vlan.vlan_nodes or b.ip not in vlan.vlan_nodes:
            continue
        component_a = vlan.vlan_node_component_map[a.ip]
        component_b = vlan.vlan_node_component_map[b.ip]
        v_a = VlanNode(a, vlan)
        v_b = VlanNode(b, vlan)
        for component in vlan.components:
            ci_seeing_a = None
            ci_seeing_b = None
            for ci, c in component.afts:
                if v_a in component.vlan_nodes or v_b in component.vlan_nodes:
                    continue
                if component_a == c:
                    ci_seeing_a = ci
                if component_b == c:
                    ci_seeing_b = ci
                if ci_seeing_a is not None and ci_seeing_b is not None:
                    break
            if (ci_seeing_a is not None and
                    ci_seeing_b is not None and
                    ci_seeing_a != ci_seeing_b):
                #print 'invalid ', vlan, link, ci_seeing_a, ci_seeing_b
                return False
    return True


def add_link(link):
    a, ai, b, bj = link

    links.add((a.ip, ai.index, b.ip, bj.index))
    discovered_interfaces.add((a.ip, ai.index))
    discovered_interfaces.add((b.ip, bj.index))

    # remove afts
    for e in a.afts.copy():
        if e[0] == ai:
            a.afts.remove(e)
    for e in b.afts.copy():
        if e[0] == bj:
            b.afts.remove(e)

    # filter pdcg
    for e in pdcg.copy():
        if ((e[0] == a and e[1] == ai)
                or (e[0] == b and e[1] == bj)
                or (e[2] == a and e[3] == ai)
                or (e[2] == b and e[3] == bj)):
            pdcg.remove(e)

    for vlan in vlans.itervalues():
        # merge components
        if (a.ip in vlan.vlan_node_component_map
                and b.ip in vlan.vlan_node_component_map):
            component_a = vlan.vlan_node_component_map[a.ip]
            component_b = vlan.vlan_node_component_map[b.ip]
            if component_a != component_b:
                # merge a into b
                component_b.vlan_nodes |= component_a.vlan_nodes
                for vn in component_a.vlan_nodes:
                    vlan.vlan_node_component_map[vn.ip] = component_b
                vlan.components.remove(component_a)

        # filter pdcg & pcg
        if a.ip in vlan.vlan_nodes:
            va = vlan.vlan_nodes[a.ip]
            vai = VlanInterface(va, ai)
            if vai in va.interfaces:
                for e in vlan.pcg.copy():
                    if ((e[0].node.ip == va.ip and e[0] == vai)
                            or (e[1].node.ip == va.ip and e[1] == vai)):
                        vlan.pcg.remove(e)
        if b.ip in vlan.vlan_nodes:
            vb = vlan.vlan_nodes[b.ip]
            vbj = VlanInterface(vb, bj)
            if vbj in vb.interfaces:
                for e in vlan.pcg.copy():
                    if ((e[0].node.ip == vb.ip and e[0] == vbj)
                            or (e[1].node.ip == vb.ip and e[1] == vbj)):
                        vlan.pcg.remove(e)

    # remove interfaces
    a.interfaces.remove(ai)
    b.interfaces.remove(bj)


def find_connections(vlan):
    cache = {}

    def aft(n, i):
        key = '%s-%s' % (n.ip, i.index)
        if key not in cache:
            cache[key] = n.aaft(i)
        return cache[key]

    def check_dumplicated(connections, pair):
        return (pair not in connections and
                (pair[1], pair[0]) not in connections)

    def add_connection(connections, pair):
        connections.add(pair)
        for ci in pair:
            ci.interface.node.node.interfaces.add(ci.interface.interface)

    for a in vlan.components:
        for b in vlan.components:
            for ai, a_other_c in a.afts:
                if a_other_c == b:
                    # condition 1
                    for bj, b_other_c in b.afts:
                        if b_other_c == a:
                            pair = (ai, bj)
                            if check_dumplicated(vlan.connections, pair):
                                add_connection(vlan.connections, pair)
                                break
                    # condition 2
                    for bj in b.ainterfaces:
                        pair = (ai, bj)
                        if not check_dumplicated(vlan.connections, pair):
                            continue
                        for ak in a.ainterfaces:
                            if ak != ai and aft(b, bj) & aft(a, ak):
                                add_connection(vlan.connections, pair)
                                break
            # condition 3
            #a_ainterfaces = a.ainterfaces
            #b_ainterfaces = b.ainterfaces
            #finded = False
            #for ai in a_ainterfaces:
                #for bj in b_ainterfaces:
                    #pair = (ai, bj)
                    #if not check_dumplicated(vlan.connections, pair):
                        #continue
                    #for az in a_ainterfaces:
                        #if az != ai and aft(b, bj) & aft(a, az):
                            #for bx in b_ainterfaces:
                                #for by in b_ainterfaces:
                                    #if (bx != by and
                                            #aft(a, ai) & aft(b, bx) and
                                            #aft(a, ai) & aft(b, by)):
                                        #finded = True
                                        #add_connection(vlan.connections, pair)
                                        #break
                                #if finded:
                                    #break
                        #if finded:
                            #break
                    #if finded:
                        #break
                #if finded:
                    #break


def apply_basic_rule(vlan):
    for ai, bj in vlan.connections:
        a = ai.interface.node.node
        b = bj.interface.node.node
        ai = ai.interface.interface
        bj = bj.interface.interface
        a.afts |= set((ai, n, v) for i, n, v in b.afts
                      if i != bj and v == vlan)
        b.afts |= set((bj, n, v) for i, n, v in a.afts
                      if i != ai and v == vlan)


def build_pdcg_pcg(vlan):
    for a in vlan.vlan_nodes.itervalues():
        for b in vlan.vlan_nodes.itervalues():
            if a == b:
                continue
            for ai in a.interfaces:
                for bj in b.interfaces:
                    #if a.ip == '10.3.0.1' or a.ip == '10.1.40.41':
                        #if b.ip == '10.3.0.1' or b.ip == '10.1.40.41':
                            #print a, ai, b, bj
                            #print a.caft(ai)
                            #print b.caft(bj)
                            #print a.caft(ai) & b.caft(bj)
                    if not (a.caft(ai) & b.caft(bj)):
                        if (bj, ai) not in vlan.pcg:
                            vlan.pcg.add((ai, bj))

                        if not ((a.aft(ai) & b.aft(bj))
                                and (bj, ai) not in vlan.pdcg):
                            vlan.pdcg.add((ai, bj))


def filter_pdcg():
    for e in pdcg.copy():
        if not check_link(e):
            pdcg.remove(e)


def select_pdcg():
    if not pdcg:
        return None
    counts = {}
    for e in pdcg:
        k1 = '%s,%s' % (e[0].ip, e[2].ip)
        k2 = '%s,%s' % (e[2].ip, e[0].ip)
        if k1 in counts:
            counts[k1][0] += 1
        elif k2 in counts:
            counts[k2][0] += 1
        else:
            counts[k1] = [0, e]

    min_count = 9999999
    min_key = None
    for k, v in counts.iteritems():
        if v[0] < min_count:
            min_count = v[0]
            min_key = k
    return counts[min_key][1]


def select_pcg():
    for vlan in vlans.itervalues():
        if not vlan.pcg:
            continue
        counts = {}
        for e in vlan.pcg:
            k1 = '%s,%s' % (e[0].node.ip, e[1].node.ip)
            k2 = '%s,%s' % (e[1].node.ip, e[0].node.ip)
            if k1 in counts:
                counts[k1][0] += 1
            elif k2 in counts:
                counts[k2][0] += 1
            else:
                counts[k1] = [0, e]

        min_count = 9999999
        min_key = None
        for k, v in counts.iteritems():
            if v[0] < min_count:
                min_count = v[0]
                min_key = k
        link = counts[min_key][1]
        link = (link[0].node.node, link[0].interface,
                link[1].node.node, link[1].interface)
        if link not in invalid_links and check_link(link):
            return link
        else:
            invalid_links.add(link)
            print 'invalid ', link
    return None


def calculate_l3_connections():
    connections = set()
    for a in nodes.itervalues():
        for b in nodes.itervalues():
            if a.ip >= b.ip:
                continue
            for ai, a_other_node in a.arp_afts:
                if a_other_node == b:
                    for bj, b_other_node in b.arp_afts:
                        if b_other_node == a:
                            connections.add((a, ai, b, bj))
                            break
    return connections


def calculate_l23_connections():
    for a in nodes.itervalues():
        for b in nodes.itervalues():
            for ai, a_other_node in a.arp_afts:
                if a_other_node == b:
                    for bj, b_other_node, vlan in b.afts:
                        if b_other_node == a:
                            a.afts.add((ai, b, vlan))
                            ai.vlanids.add(vlan.id)
                            vlan_node = VlanNode(a, vlan)
                            vlan.vlan_nodes[a.ip] = vlan_node
                            component = Component(vlan_node)
                            vlan.components.add(component)
                            vlan.vlan_node_component_map[vlan_node.ip] = component
                            break


nodes, vlans = initialize(raw_info)

print 'Initialization completed'
links = set()
invalid_links = set()
discovered_interfaces = set()
pdcg = set()

# calculate layer 3 connections
l3_connections = calculate_l3_connections()
l23_connections = calculate_l23_connections()

# merge pdcg
for vlan in vlans.itervalues():
    print vlan, '\t',
    print 'finding connections',
    sys.stdout.flush()
    find_connections(vlan)
    print ', extending',
    sys.stdout.flush()
    apply_basic_rule(vlan)
    print ', buiding pdcg & pcg',
    sys.stdout.flush()
    build_pdcg_pcg(vlan)
    pdcg |= set((ai.node.node, ai.interface, bj.node.node, bj.interface)
                for ai, bj in vlan.pdcg)
    print '...Done'

# remove dumplicated pdcg
print 'Removing dumplicated pdcg',
old_pdcg = pdcg.copy()
pdcg = set()
for e in old_pdcg:
    if (e[2], e[3], e[0], e[1]) not in pdcg:
        pdcg.add(e)
del old_pdcg
print '...Done'

# filter pdcg
print 'filting pdcg'
for a, ai, b, bj in pdcg.copy():
    for vlan in vlans.itervalues():
        if a.ip in vlan.vlan_nodes and b.ip in vlan.vlan_nodes:
            va = vlan.vlan_nodes[a.ip]
            vb = vlan.vlan_nodes[b.ip]
            vai = VlanInterface(va, ai)
            vbj = VlanInterface(vb, bj)
            if (vai in va.interfaces and vbj in vb.interfaces
                    and (vai, vbj) not in vlan.pdcg
                    and (vbj, vai) not in vlan.pdcg):
                pdcg.remove((a, ai, b, bj))
                break
print 'Done'

# !!!!!!!!!!!!!!!!!
print 'Start Calculation'
while True:
    finded = False
    for vlan in vlans.itervalues():
        while True:
            link = reduction_process(vlan.components)
            if not link:
                break
            if check_link(link):
                print vlan, link
                add_link(link)
                finded = True
            else:
                invalid_links.add(link)
                print 'invalid ', link
    if not finded:
        filter_pdcg()
        link = select_pdcg()
        if link:
            print 'PDCG ', link
            add_link(link)
        else:
            link = select_pcg()
            if link:
                print 'PCG ', link
                add_link(link)
            else:
                break

# layer 3
for link in l3_connections:
    if check_link(link):
        link = (link[0].ip, link[1].index, link[2].ip, link[3].index)
        if ((link[0], link[1]) not in discovered_interfaces
                and (link[2], link[3]) not in discovered_interfaces):
            links.add(link)
            discovered_interfaces.add((link[0], link[1]))
            discovered_interfaces.add((link[2], link[3]))
            print 'L3 ', link
#for link in l23_connections:
    #if check_link(link):
        #link = (link[0].ip, link[1].index, link[2].ip, link[3].index)
        #if ((link[0], link[1]) not in discovered_interfaces
                #and (link[2], link[3]) not in discovered_interfaces):
            #links.add(link)
            #discovered_interfaces.add((link[0], link[1]))
            #discovered_interfaces.add((link[2], link[3]))
            #print 'L23 ', link


sys.stdout = old_stdout
logfile.close()

f = open(info_path, 'w')
json.dump(list(links), f)
f.close()
