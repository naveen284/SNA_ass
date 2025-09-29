#include <stdio.h>
#include <string.h>

int main(void) {
    char buffer[10];
    char input[256];

    printf("Enter a string: ");
    if (fgets(input, sizeof(input), stdin) == NULL) return 1;
    size_t len = strlen(input);
    if (len > 0 && input[len-1] == '\n') input[len-1] = '\0';

    /* Safe copy: keep last byte for null terminator */
    strncpy(buffer, input, sizeof(buffer)-1);
    buffer[sizeof(buffer)-1] = '\0';

    printf("You entered (safe): %s\n", buffer);
    return 0;
}
