#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

int main(int argc, char *argv[])
{
    FILE *f = NULL;
    char cmd[1024];
    char buf[1024];
    int len;
    int i, j;

    if (argc == 3) {
        if (!strcmp(argv[1], "add")) {
            f = fopen("/home/probe/.ssh/authorized_keys", "a");
            fwrite(argv[2], strlen(argv[2]), 1, f);
            fclose(f);
        } else if (!strcmp(argv[1], "delete")) {
            setuid(0);
            len = strlen(argv[2]);
            j = 0;
            for (i = 0; i != len; i++) {
                if (argv[2][i] == '\"' || argv[2][i] == '\'' || argv[2][i] == '/') {
                    buf[j++] = '\\';
                }
                buf[j++] = argv[2][i];
            }
            buf[j] = '\0';
            sprintf(cmd, "sed -i \"/%s/d\" /home/probe/.ssh/authorized_keys", buf);
            printf("%s", cmd);
            system(cmd);
        }
    }
}
