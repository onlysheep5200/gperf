import os
import re
import sys
import types
try:
    import json
except ImportError:
    import simplejson as json

from pexpect import pexpect


class ExpectException(Exception):
    pass


def strtr(strng, replace):
    buf, i, n = [], 0, len(strng)
    while i < n:
        for s, r in replace.items():
            if strng[i:len(s) + i] == s:
                buf.append(r)
                i += len(s)
                break
        else:
            buf.append(strng[i])
            i += 1
    return ''.join(buf)


class Expect(object):
    OPERATION_LOG_SEPERATOR = 'HI(S*(*#QHUFSNDF(U#_'

    def __init__(self, json_str):
        if isinstance(json_str, basestring):
            args = json.loads(json_str)
        else:
            args = json_str

        try:
            self.cmd = args['cmd']
            if 'args' in args:
                self.args = args['args']
            else:
                self.args = []
            if 'timeout' in args:
                self.timeout = args['timeout']
            else:
                self.timeout = None
            self.debug = 'debug' in args and args['debug']
            self.operations = args['operations']
            if 'context' in args:
                self.context = dict(args['context'])
            else:
                self.context = {}
            if 'linesep' in args:
                self.linesep = args['linesep']
            else:
                self.linesep = os.linesep
        except KeyError, e:
            raise ExpectException("Missing arg '%s'" % e.args[0])

        self.last_return_value = None
        self.logfile = None

    def create_expect(self):
        kwargs = {}
        if self.timeout is not None:
            kwargs['timeout'] = self.timeout

        self.ex = pexpect.spawn(self.cmd, self.args, **kwargs)

        if self.debug:
            self.logfile = os.tmpfile()
            self.ex.logfile_read = self.logfile

        if not hasattr(self.ex, 'ret'):
            def ret(self, value):
                self.ret_value = value

            self.ex.ret_value = None
            self.ex.ret = types.MethodType(ret, self.ex)

        if not hasattr(self.ex, 'expect_ex'):
            def expect_ex(self, opts):
                wait_and_send_patterns, wait_and_send_cmds = zip(*opts[:-1])
                wait_and_send_patterns = list(wait_and_send_patterns)
                end_pattern = opts[-1]
                if not isinstance(end_pattern, basestring):
                    end_pattern = end_pattern[0]
                wait_and_send_patterns.insert(0, end_pattern)

                while True:
                    index = self.expect(wait_and_send_patterns)
                    if index == 0:
                        break
                    else:
                        self.sendline(wait_and_send_cmds[index - 1])

            self.ex.expect_ex = types.MethodType(expect_ex, self.ex)

        if not hasattr(self.ex, 'linesep'):
            self.ex.linesep = self.linesep

            def sendline(self, s=''):
                n = self.send(s)
                n = n + self.send(self.linesep)
                return n
            self.ex.sendline = types.MethodType(sendline, self.ex)

    def close_expect(self):
        if self.debug:
            self.logfile.close()

        self.ex.close()

    def run(self):
        self.create_expect()

        ret = {}
        operation_index = 0
        try:
            try:
                if not self.operations:
                    raise ExpectException('No operations to execute')

                if isinstance(self.operations, basestring):
                    self.operations = [self.operations]
                for operation_index, operation in enumerate(self.operations):
                    if isinstance(operation, basestring):
                        operation = [operation]
                    if len(operation) == 1:
                        operation = ['auto', operation[0]]

                    t, v = operation
                    if t in ['file', 'pythonfile', 'templatefile']:
                        try:
                            f = open(v)
                            commands = f.read()
                            f.close()
                        except IOError, e:
                            raise ExpectException(
                                "Error while execute '%s': %s" % (v, e))
                        v = commands
                        if t == 'file':
                            t = 'auto'
                        elif t == 'pythonfile':
                            t = 'python'
                        elif t == 'templatefile':
                            t = 'template'

                    if t == 'auto':
                        v = v.lstrip()
                        if v[0:3] == '#py':
                            t = 'python'
                        else:
                            t = 'template'

                    if t == 'python':
                        self.run_python(v)
                    elif t == 'template':
                        self.run_template(v)

                    if self.debug:
                        self.logfile.write(self.OPERATION_LOG_SEPERATOR)
            finally:
                if self.debug:
                    self.logfile.seek(0)
                    ret['log'] = (self.logfile.read()
                                  .split(self.OPERATION_LOG_SEPERATOR))

                self.close_expect()
        except Exception, e:
            import traceback
            ret.update(**{
                'error': True,
                'operation_index': operation_index,
                'message': str(e),
                'trackstack': traceback.format_exc(),
            })
        else:
            ret.update(**{
                'error': False,
                'last_match': self.ex.after,
                'last_prematch': self.ex.before,
                'return': self.last_return_value,
            })

        return ret

    def run_python(self, commands):
        self.context.update(**{
            'pexpect': pexpect,
            'ex': self,
            'proc': self.ex,
            'last_return_value': self.last_return_value,
        })
        try:
            commands = commands.replace('\r\n', '\n')
            exec commands in self.context
        except pexpect.EOF:
            raise ExpectException("Get EOF while execute python script"+commands)
        except pexpect.TIMEOUT:
            raise ExpectException("Timeout while execute python script")

        self.last_return_value = self.ex.ret_value

    def run_template(self, tpl_str):
        for line in tpl_str.splitlines():
            cmd = line.strip()
            if cmd == '':
                continue

            cmd_3 = cmd[0:3]
            if cmd_3 == '$$$':
                self.ex.sendline('')
            elif cmd_3 == '<<<':
                origin = cmd
                cmd = cmd[3:]
                timeout = -1

                match = re.match(r'\[\[([_a-zA-Z]+)=(.+)\]\]', cmd)
                if match:
                    cmd = cmd[len(match.group(0)):]
                    key = match.group(1)
                    value = match.group(2)

                    if key == 'timeout':
                        timeout = int(value)

                cmd = self.replace_context(cmd)
                index = self.ex.expect([cmd, pexpect.EOF, pexpect.TIMEOUT],
                                       timeout)
                if index == 1:
                    raise ExpectException("Get EOF while expect '%s'" %
                                          origin)
                elif index == 2:
                    raise ExpectException("Timeout while expect '%s'" %
                                          origin)
            elif cmd_3 == '###' or cmd_3 == '---':
                pass
            else:
                cmd = self.replace_context(cmd)
                self.ex.sendline(cmd)
                #self.logfile.write('"""%s"""' % cmd)
        self.last_return_value = self.ex.after

    def replace_context(self, cmd):
        context_replace_dict = dict([('{%s}' % k, v)
                                     for k, v in self.context.items()])
        return strtr(cmd, context_replace_dict)


def run_with_args(args):
    try:
        ex = Expect(args)
        result = ex.run()
    except Exception, e:
        import traceback
        result = {
            'trackstack': traceback.format_exc(),
            'error': True,
            'message': str(e),
        }
    return result

if __name__ == '__main__':
    print json.dumps(run_with_args(sys.argv[1]))
    #login_operation = """
##py
#proc.linesep = '\\r'
#proc.expect(['P'])
#proc.sendline('JBNzzz@NOC-0')
#proc.expect(['>'])
#proc.sendline('en')
#proc.expect(['P'])
#proc.sendline('JBNzzz@NOC-0')
#proc.expect(['#'])
#"""
    #save_operation = """
##py
#import datetime
#date_str = datetime.datetime.now().strftime('%m%d%H%M')

#proc.sendline('copy startup-config tftp://%s/cfg/%s/%s' %
              #(server, ip, date_str))
#while True:
    #index = proc.expect(['\]', 'bytes copied'])
    #if index == 0:
        #proc.sendline('')
    #elif index == 1:
        #break
#"""

    #result = run_with_args({
        #'cmd': 'telnet',
        #'args': ['10.0.0.1'],
        #'debug': True,
        #'operations': [
            #login_operation,
            #save_operation,
        #],
    #})
    #print result
    #print ''.join(result['log'])
