#py
import re

proc.sendline('zte')
proc.expect('assword:')
proc.sendline('zte')
proc.expect('#')

olt_interface_list = {}
for dirt_key in interface:
	olt = interface[dirt_key]
	olt_interface = olt["interface"]
	olt_interface_list[olt["ifIndex"]] = []
	proc.sendline('show onu all-status %s' % olt_interface)
	while True:
		index = proc.expect(['%Code 40529','Invalid','Status','--More--', sysname + '#'])
		if index==0 or index==1:
			result_1 = False
			proc.expect(sysname + '#')
			break
		elif index==4:
			for line in proc.before.strip().replace('\x08','').splitlines():
				parts = line.split()
				if len(parts) != 3:
					continue
				e = {}
				e['Onu_Interface'] = parts[0]
				e['Regstatus'] = parts[1]
				e['Status'] = parts[2]
				olt_interface_list[olt["ifIndex"]].append(e['Onu_Interface'])
			break
		elif index==3:
			for line in proc.before.split('\n'):
				parts = line.split()
				if len(parts) != 3:
					continue
				e = {}
				e['Onu_Interface'] = parts[0]
				e['Regstatus'] = parts[1]
				e['Status'] = parts[2]
				olt_interface_list[olt["ifIndex"]].append(e['Onu_Interface'])
			proc.send(' ')
		elif index==2:
			pass

onu_detail = {}
final_result = {}
tes = []
for olt_ifindex in olt_interface_list:
	final_result[olt_ifindex] = {}
	for onu in olt_interface_list[olt_ifindex]:
		proc.sendline('show onu detail-info %s' % onu)
		result = {}
		while True:
			index = proc.expect(['Invalid','Authpass', '--More--', sysname + '#'])
			if index==0:
				result = False
				proc.expect(sysname + '#')
				break
			elif index==1:
				for line in proc.before.split('\n')[2:]:
					parts = line.split(':')
					result[parts[0]] = ':'.join(parts[1:]).strip().decode('gbk')
			elif index == 2:
				proc.send(' ')
			elif index == 3:
				break

		if result:
			mac = ''.join(result["MAC"].split('.'))
			final_result[olt_ifindex][mac] = result
			proc.sendline('show interface %s' % onu)
			while True:
				index_1 = proc.expect(['Invalid','Interface','--More--', sysname + '#'])
				if index_1 == 0:
					break
				elif index_1 == 1:
					try:
						final_result[olt_ifindex][mac]['input_rate'] = re.search('Input rate :\s*(\d+)\sbps', proc.before).group(1)
						final_result[olt_ifindex][mac]['output_rate'] = re.search('Output rate:\s*(\d+)\sbps', proc.before).group(1)
					except:
						final_result[olt_ifindex][mac]['input_rate'] = int(re.search('Input rate :\s*(\d+)\sBps', proc.before).group(1)) * 8
						final_result[olt_ifindex][mac]['output_rate'] = int(re.search('Output rate:\s*(\d+)\sBps', proc.before).group(1)) * 8
				elif index_1 == 2:
					proc.send(' ')
				elif index_1 == 3:
					break

proc.ret_value = final_result
