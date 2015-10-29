#py
proc.sendline('show mac address-table')
index = proc.expect(['----    -----------       --------    -----',
             r'\+--------------------------'])
result = []
if index == 0:
    while True:
        index = proc.expect(['--More--', 'Total Mac'])
        lines = proc.before.strip().replace('\x08', '').splitlines()
        for line in lines:
            cols = line.strip().split()
            if len(cols) == 4:
                mac = []
                cols[1] = cols[1].replace('.', '')
                for i in range(6):
                    mac.append(cols[1][2*i]+cols[1][2*i+1])
                cols[1] = ':'.join(mac)
                result.append(dict(zip(('vlan', 'mac', 'type', 'ports'), cols)))
        if index == 0:
            proc.send(' ')
        elif index == 1:
            break
elif index == 1:
    while True:
        index = proc.expect(['--More--', '#'])
        lines = proc.before.strip().replace('\x08', '').splitlines()
        for line in lines:
            cols = line.strip().split()
            length = len(cols)
            if length == 6 or length == 7:
                if length == 7:
                    cols = cols[1:]
                mac = []
                cols[1] = cols[1].replace('.', '')
                for i in range(6):
                    mac.append(cols[1][2*i]+cols[1][2*i+1])
                cols[1] = ':'.join(mac)
                cols = (cols[0], cols[1], cols[2], cols[5])
                result.append(dict(zip(('vlan', 'mac', 'type', 'ports'), cols)))
        if index == 0:
            proc.send(' ')
        elif index == 1:
            break
proc.ret_value = result
