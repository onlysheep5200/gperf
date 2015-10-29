if ex.cmd == 'telnet':
    while True:
        index = proc.expect(['sername:', 'login:', 'assword:', '>'])
        if index == 0 or index == 1:
            proc.sendline(username)
        elif index == 2:
            proc.sendline(password)
        elif index == 3:
            break
elif ex.cmd == 'ssh':
    while True:
        index = proc.expect([r'connecting (yes/no)\?', 'assword:', '>'])
        if index == 0:
            proc.sendline('yes')
        elif index == 1:
            proc.sendline(password)
        elif index == 2:
            break
proc.sendline('en')
proc.expect(['assword:'])
proc.sendline(setpassword)
proc.expect(['#'])

