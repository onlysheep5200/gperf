#py
proc.sendline('show arp')
proc.expect('Type   Interface')
result = []
while True:
    index = proc.expect(['--More--', '#'])
    lines = proc.before.strip().replace('\x08', '').splitlines()
    for line in lines:
        cols = line.strip().split()
        length = len(cols)
        if length == 6 or length == 5:
            if cols[3].find('.') == -1:
                continue
            cols[3] = cols[3].replace('.', '')
            mac = []
            for i in range(6):
                mac.append(cols[3][2*i]+cols[3][2*i+1])
            cols[3] = ':'.join(mac)
            if length == 5:
                cols.append('0')
            result.append(dict(zip(
                ('protocol', 'ip', 'age', 'mac', 'type', 'interface'), cols)))

    if index == 0:
        proc.send(' ')
    elif index == 1:
        break
proc.ret_value = result
