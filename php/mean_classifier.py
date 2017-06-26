#!/usr/bin/env python3

import sys, os
import numpy as np
import matplotlib.pyplot as plt

path = sys.argv[1]
text = []
with open(path) as f:
    text = f.read()

lines = text.split("\n")
confidences = {}
for line in lines:
    if not line: continue
    if "Fatal error" in line: continue
    if "BAD" in line: continue
    splitted = line.split(",")
    if(splitted[-2] not in confidences):
        confidences[splitted[-2]] = []
    confidences[splitted[-2]].append(float("%.1f" % (float(splitted[-1]))))

#plt.plot(sorted(confidences))
#plt.show()

for k in confidences:
    print("Confidence for key", k)
    print("Confidence mean ", np.mean(confidences[k]))
    print("max", np.max(confidences[k]))
    print("min", np.min(confidences[k]))
