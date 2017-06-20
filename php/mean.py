#!/usr/bin/env python3

import sys, os
import numpy as np
import matplotlib.pyplot as plt

path = sys.argv[1]
text = []
with open(path) as f:
    text = f.read()

lines = text.split("\n")
confidences = []
for line in lines:
    if not line: continue
    if "Fatal error" in line: continue
    if "BAD" in line: continue
    tmp = line.split(",")
    confidences.append(float("%.1f" % (float(tmp[-2]))))

plt.plot(sorted(confidences))
plt.show()
print("mean", np.mean(confidences))
print("max", np.max(confidences))
print("min", np.min(confidences))
