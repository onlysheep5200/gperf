#py
import re
proc.sendline('zte')
proc.expect('assword:')
proc.sendline('zte')
proc.expect('#')

proc.sendline('show card')
proc.expect('----')
array_n = []
for array_name in proc.before.split():
	array_n.append(array_name)
array_n = array_n[2:]
index1 = proc.expect(['--More--','#'])
if index1 == 0:
	proc.send(' ')
	proc.expect('#')
array_value = []
for line in proc.before.split('\n'):
	array_value.append(line)
array_value = array_value[1:-1]

result = []
for line in array_value:
	line_result = {}
	v = line.split()
	for (value,name) in map(None,v,array_n):
		line_result[name] = value
	result.append(line_result)

for line in result:
	if line['Status'] == None:
		if line['SoftVer'] != None:
			line['Status'] = line['SoftVer']
		elif line['HardVer'] != None:
			line['Status'] = line['HardVer']

proc.sendline('show temperature')
proc.expect('Slot')
index = proc.expect(['--More--','#'])
if index == 0:
	proc.send(' ')
raw_t = []
for line in proc.before.split('\n'):
	raw_t.append(line)
t = raw_t[2:-2]

te = {}
for line in t:
	a = []
	for k in line.split():
		a.append(k)
	te[int(a[0])] = a[1]
for b in result:
	if te.has_key(int(b['Slot'])):
		b['Temperature'] = te[int(b['Slot'])]
	else:
		b['Temperature'] = 'N/A.'
if index == 0:
	proc.expect('#')

proc.sendline('show processor')
proc.expect('------')
k = []
for line in proc.before.split():
	k.append(line)
k = k[2:]
proc.expect('#')
q = []
for line in proc.before.split('\n'):
	q.append(line)
q = q[1:-1]
w = []
for line in q:
	lr = {}
	for (name,value) in map(None,k,line.split()):
		lr[name] = value
	w.append(lr)
e = {}
for s in w:
	key = s['Slot']+'/'+s['Rack']+'/'+s['Shelf']
	del s['Slot']
	del s['Rack']
	del s['Shelf']
	e[key] = s
ee = {'CPU(5s)':'N/A','CPU(1m)':'N/A','CPU(5m)':'N/A','PhyMem(MB)':'N/A','Memory':'N/A'}
for s in result:
	key = s['Slot']+'/'+s['Rack']+'/'+s['Shelf']
	if e.has_key(key) :
		s.update(e[key])
	else:
		s.update(ee)

proc.ret_value = result
