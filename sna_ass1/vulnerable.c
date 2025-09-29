#include <stdio.h>
#include <string.h>

int main(void) {
    char buffer[10];
    char input[256];

    printf("Enter a string: ");
    if (fgets(input, sizeof(input), stdin) == NULL) return 1;
    size_t len = strlen(input);
    if (len > 0 && input[len-1] == '\n') input[len-1] = '\0';

    /* Intentional unsafe copy to demonstrate overflow */
    strcpy(buffer, input);

    printf("You entered: %s\n", buffer);
    return 0;
}
