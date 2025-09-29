Buffer Overflow Demo
Files:
- vulnerable.c
- safe.c
- test_input.txt

Compile:
gcc -o vulnerable vulnerable.c
gcc -o safe safe.c

Run:
./vulnerable    # paste long input to trigger overflow
./safe
