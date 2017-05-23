INCLUDES = -I. -I/usr/local/include
LIBS = -L./ -L/usr/local/lib -lm -lfst -ldl

fstprintstrings: fstprintstrings.cc fstprintstrings.h
	g++ -std=gnu++0x $(LIBS) $(INCLUDES) -o $@ fstprintstrings.cc
