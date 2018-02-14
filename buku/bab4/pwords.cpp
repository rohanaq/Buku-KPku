#include <cstdio>
#include <algorithm>
#include <vector>
#include <string>
#include <cstring>

using namespace std;

vector<int> set_bit[(1 << 11)];
string S[2];
int charLastPos[2][50];
int charFirstPos[2][50];
int X, bound;
int memoF[2][(1 << 10) + 2][250 + 2];
int memoG[2][(1 << 10) + 2][250 + 2];
int maxMask[2];


void preprocess();
bool duplicate_rule1(int idx, int mask, int charIdx);
bool duplicate_rule2(int idx, int mask, int charIdx);
bool duplicate_rule3(int idx, int mask, int charIdx);
long long F1(int idx, int mask, int charIdx, int dist);
long long F(int idx, int mask, int dist);
long long G1(int idx, int mask, int charIdx, int dist);
long long G2(int idx, int mask, int charIdx, int dist);
long long G3(int idx, int mask, int charIdx, int dist);
long long G(int idx, int mask, int dist);
long long solveProblem();

bool isBitOn(int val, int pos) {
	return ((val) & (1<<(pos)));
}

void preprocess() {
	for (int i = 0; i < (1 << 10); i++) {
		for (int j = 0; j < 10; j++) {
			if (isBitOn(i, j)) 
				set_bit[i].
				push_back(j);
		}
	}
}

bool duplicate_rule1(int idx, int mask, int 
	charIdx) {	
	return (charIdx < S[idx].length() - 1
		&& (S[idx][charIdx] != S[idx][
			charIdx + 1]
		|| (S[idx][charIdx] == S[idx][
			charIdx + 1]
		&& !isBitOn(mask, charIdx + 1))
			));
}

bool duplicate_rule2(int idx, int mask, int 
	charIdx) {
	return (charIdx < S[idx].length() - 1
		&& (charFirstPos[idx][(S[idx][
			charIdx] + 1) - 'a'] == -1 
		|| (charFirstPos[idx][(S[idx][
			charIdx]) + 1 - 'a'] != -1 
		&& !isBitOn(mask, charFirstPos[
			idx][S[idx][charIdx] + 1 - 
			'a']))));
}

bool duplicate_rule3(int idx, int mask, int 
	charIdx) {
	return (charIdx > 0
		&& (charLastPos[idx][(S[idx][
			charIdx] - 1) - 'a'] == -1 
		|| (charLastPos[idx][(S[idx][
			charIdx] - 1) - 'a'] != -1 
		&& isBitOn(mask, charLastPos[idx
			][(S[idx][charIdx] - 1) - 
			'a']))));
}

long long F1(int idx, int mask, int charIdx, int 
	dist) {
	int curIdx = S[idx].length() - 
	__builtin_popcount(mask);
	if (charIdx == S[idx].length() - 1 || 
		duplicate_rule1(idx, mask, charIdx)
		) {
		return F(idx, mask - (1 << 
			charIdx), dist + abs(S[idx]
			[charIdx] - S[idx][curIdx
				]));
	}
	return 0;
}

long long F(int idx, int mask, int dist) {
	if (dist > bound || (mask == 0 && dist 
		!= bound)) return 0;
	if (mask == 0 && dist == bound) 
		return 1;
	if (memoF[idx][mask][dist] != -1) 
		return memoF[idx][mask][dist];
	int NSB = set_bit[mask].size();
	long long ret = 0;
	for (int i=0; i<NSB; i++) {
		if (set_bit[mask][i] >= 
			S[idx].length()) break;
		ret += F1(idx, mask, 
			set_bit[mask][i], dist);
	}
	return memoF[idx][mask][dist] = ret;
}

long long G1(int idx, int mask, int charIdx, int 
	dist) {
	int curIdx = S[idx].length() - 
		__builtin_popcount(mask);
	if (charIdx == S[idx].length() - 1 || 
		duplicate_rule1(idx, mask, 
		charIdx)) {
		return G(idx, mask - (1 << 
			charIdx), dist + abs(
			S[idx][charIdx] - 
			S[idx][curIdx]));
	}
	return 0;
}

long long G2(int idx, int mask, int charIdx, int 
	dist) {
	int curIdx = S[idx].length() - 
		__builtin_popcount(mask);
	if (charIdx == S[idx].length() - 1 || 
		(duplicate_rule2(idx, mask, 
		charIdx) && duplicate_rule1(idx, 
		mask, charIdx))) {
		return F(idx, mask - (1 << 
			charIdx), dist + abs((
			S[idx][charIdx] + 1) -
			S[idx][curIdx]));
	}
	return 0;
}

long long G3(int idx, int mask, int charIdx, int 
	dist) {
	int curIdx = S[idx].length() - 
		__builtin_popcount(mask);
	if ((charIdx == S[idx].length() - 1 || 
		duplicate_rule1(idx, mask, 
		charIdx)) && (charIdx == 0 || 
		duplicate_rule3(idx, mask, 
		charIdx)) ) {
		return F(idx, mask - (1 << 
			charIdx), dist + abs((
			S[idx][charIdx] - 1) -
			S[idx][curIdx]));
	}
	return 0;
}

long long G(int idx, int mask, int dist) {
	if (dist > bound || mask == 0) return 0;
	if (memoG[idx][mask][dist] != -1) return 
		memoG[idx][mask][dist];
	int NSB = set_bit[mask].size();
	long long ret = 0;
	for (int i=0; i<NSB; i++) {
		if (set_bit[mask][i] >= S[idx].
			length()) break;
		ret += G1(idx, mask, set_bit[mask
			][i], dist);
		ret += G2(idx, mask, set_bit[mask
			][i], dist);
		ret += G3(idx, mask, set_bit[mask
			][i], dist);
	}
	return memoG[idx][mask][dist] = ret;	
}

long long solveProblem() {
	long long ret = 0;
	for (int dist = 0; dist <= min(250, X);
		dist++) {
		int rem = X - dist;
		if (rem > 250) continue;
		if (rem < 0) break;        
		ret += F(0, maxMask[0], bound - 
			dist) * G(1, maxMask[1], 
			bound - rem);
		ret += G(0, maxMask[0], bound - 
			dist) * F(1, maxMask[1], 
			bound - rem);
	}
	return ret;
}

void readInput() {
	char dummySt[30];
	scanf("%s", dummySt);
	S[0] = dummySt;
	scanf("%s", dummySt);
	S[1] = dummySt;
	scanf("%d", &X);
}

void writeOutput(int tc, long long ans) {
	printf("Case %d: %lld\n", tc, ans);
}

void init() {
	memset(charLastPos, -1, sizeof 
		charLastPos);
	memset(charFirstPos, -1, sizeof 
		charFirstPos);
	memset(memoF, -1, sizeof memoF);
	memset(memoG, -1, sizeof memoG);
	for (int idx = 0; idx < 2; idx++) {
		sort(S[idx].begin(), S[idx].end()
			);
		maxMask[idx] = (1 << S[idx].
			length()) - 1;
		for (int i = 0; i < S[idx].length
			(); i++) {
			charLastPos[idx][S[idx][i
				] - 'a'] = i;
			if (charFirstPos[idx][S[
				idx][i] - 'a'] == 
				-1) {
				charFirstPos[idx
					][S[idx][i]
					- 'a'] = i
					;
			}
		}
	}
	bound = min(X, 250);
}

int main() {
	preprocess();
	int t;
	scanf("%d", &t);
	for (int tc=1; tc<=t; tc++) {
		readInput();
		init();
		long long ans = solveProblem();
		writeOutput(tc, ans);
	}
	return 0;
} 