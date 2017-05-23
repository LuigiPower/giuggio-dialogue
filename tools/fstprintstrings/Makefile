INCLUDES = -I. -I/usr/local/include
LIBS = -L./ -L/usr/local/lib -lm -lfst -ldl

fstprintstrings: fstprintstrings.cc fstprintstrings.h
	g++ $(LIBS) $(INCLUDES) -o $@ fstprintstrings.cc
