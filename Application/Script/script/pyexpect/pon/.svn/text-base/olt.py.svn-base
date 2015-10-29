#py
import re
proc.sendline('zte')
proc.expect('assword:')
proc.sendline('zte')
proc.expect('#')
result = {}

for dirt_key in interface:
	olt_in = interface[dirt_key]
	olt_interface = interface[dirt_key]["interface"]
	proc.sendline('show interface %s' % olt_interface)
	while True:
		index = proc.expect(['Invalid','Interface','--More', sysname + '#'])
		if index==0:
			proc.expect(sysname + '#')
			break
		elif index==1:
			try:
				olt_in['input_rate'] = re.search('Input rate :\s*(\d+)\sbps', proc.before).group(1)
				olt_in['output_rate'] = re.search('Output rate:\s*(\d+)\sbps', proc.before).group(1)
			except:
				olt_in['input_rate'] = int(re.search('Input rate :\s*(\d+)\sBps', proc.before).group(1)) * 8
				olt_in['output_rate'] = int(re.search('Output rate:\s*(\d+)\sBps', proc.before).group(1)) * 8
		elif index==2:
			proc.send(' ')
		elif index==3:
			break

	proc.sendline('show interface optical-module-info %s' % olt_interface)
	while True:
		index = proc.expect(['Invalid','Diagnostic-info:','--More', sysname + '#'])
		if index==0:
			proc.expect(sysname + '#')
			break
		elif index==1:
			proc.expect('Alarm-thresh:')

			olt_in['RxPower'] = re.search('RxPower\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
			olt_in['TxPower'] = re.search('TxPower\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
			olt_in['Bias_Current'] = re.search('Bias-Current\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
			olt_in['Laser_rate'] = re.search('Laser-Rate\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
			olt_in['Temperature'] = re.search('Temperature\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
			olt_in['Supply_Vol'] = re.search('Supply-Vol\s*:\s*(n/a|[^\(\s]*)', proc.before).group(1)
		elif index==2:
			proc.send(' ')
		elif index==3:
			break

proc.ret_value = interface
