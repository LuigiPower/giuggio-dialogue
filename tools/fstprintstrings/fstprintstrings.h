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

#ifndef FST_AUX_FSTPRINTSTRINGS_H__
#define FST_AUX_FSTPRINTSTRINGS_H__

#include <vector>
#include <fst/fst.h>

DECLARE_bool(use_separator);
DECLARE_bool(print_weight);
DECLARE_string(isymbols);
DECLARE_string(osymbols);

namespace fst {

template <class Arc>
class FstPrintStrings {
public:
  typedef typename Arc::Weight Weight;  

  explicit FstPrintStrings(Fst<Arc>* fst) 
    : isymbols_(NULL), osymbols_(NULL){
    if (!fst->Properties(kAcyclic, true)) {
      LOG(FATAL) << "Fst must be acyclic";
    }
    fst_ = fst;
    if (FLAGS_isymbols == "")  {
      isymbols_ = fst_->InputSymbols();
    } else {
      isymbols_ = SymbolTable::ReadText(FLAGS_isymbols);
      if (!isymbols_) exit(1);
    }
    if (FLAGS_osymbols == "")  {
      osymbols_ = fst_->OutputSymbols();
    } else {
      osymbols_ = SymbolTable::ReadText(FLAGS_osymbols);
      if (!osymbols_) exit(1);
    }
    vector<int> iout;
    vector<int> oout;
    Weight w = Weight::One();
    Walk(fst_->Start(), iout, oout, w);
  }

  ~FstPrintStrings() {}

private:
  void Walk(int state,
	    vector<int> iout,
	    vector<int> oout,
	    Weight w);
  Fst<Arc>* fst_;
  const SymbolTable* isymbols_;
  const SymbolTable* osymbols_;
  DISALLOW_COPY_AND_ASSIGN(FstPrintStrings);
};  // class FstPrintStrings

template <class Arc>
void FstPrintStrings<Arc>::Walk(int state,
				vector<int> iout,
				vector<int> oout,
				Weight w) {
  if (fst_->Final(state) != Weight::Zero()) {
    for (int i = 0; i < iout.size(); ++i) {
      if (iout[i]) {
	if (isymbols_)
	  cout << isymbols_->Find(iout[i]);
	else
	  cout << iout[i];
      }
      if (FLAGS_use_separator && i != iout.size() - 1) cout << " ";
    }
    cout << "\t";
    for (int i = 0; i < oout.size(); ++i) {
      if (oout[i]) {
	if (osymbols_)
	  cout << osymbols_->Find(oout[i]);
	else
	  cout << oout[i];
      }
      if (FLAGS_use_separator && i != oout.size() - 1) cout << " ";
    }
    if (FLAGS_print_weight) {
      cout << "\t";
      cout << w.Value();
    }
    cout << endl;
  }
  for (ArcIterator<Fst<Arc> > aiter(*fst_, state);
       !aiter.Done();
       aiter.Next()) {
    const Arc arc = aiter.Value();
    iout.push_back(arc.ilabel);
    oout.push_back(arc.olabel);
    Walk(arc.nextstate, iout, oout, Times(w, arc.weight));
    iout.pop_back();
    oout.pop_back();
  }
}

}  // namespace fst

#endif  //  FST_AUX_FSTPRINTSTRINGS_H__  
