// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// Copyright 2011
// Author: rws@xoba.com (Richard Sproat)

#include <fst/mutable-fst.h>
#include <fstprintstrings.h>

DEFINE_bool(use_separator, false, "Use space as a separator");
DEFINE_bool(print_weight, false, "Print cumulated weight");
DEFINE_string(isymbols, "", "Input label symbol table");
DEFINE_string(osymbols, "", "Output label symbol table");

int main(int argc, char **argv) {
  using fst::FstPrintStrings;
  using fst::MutableFst;
  using fst::StdArc;

  string usage = "Prints out strings for acyclic machines.\n\n  Usage: ";
  usage += argv[0];
  usage += " [binary.fst]\n";

  std::set_new_handler(FailedNewHandler);
  SetFlags(usage.c_str(), &argc, &argv, true);
  if (argc > 2) {
    ShowUsage();
    return 1;
  }

  string in_name = (argc > 1 && strcmp(argv[1], "-") != 0) ? argv[1] : "";

  // Just to get this working for now
  MutableFst<StdArc> *fst = MutableFst<StdArc>::Read(in_name);
  if (!fst) return 1;
  FstPrintStrings<StdArc> print_strings(fst);

  return 0;
}
