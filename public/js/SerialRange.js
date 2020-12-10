
function findPrefix(sa, sb) {
	if (sa.length !== sb.length) {
		return -1; 
	}
	var idx = 1;
	while ((idx <= sa.length) && (sa.substring(0, idx) === sb.substring(0, idx))) {
		idx++;
	}
	idx--;
	return idx;
}

function findSuffix(sa, sb, rx) {
	if (sa.length !== sb.length) {
		return -1; 
	}
	var idx = sa.length - 1;
	var base = parseInt(sa.charAt(idx), rx);
	base = isNaN(base) ? 100000 : base;
	while ((idx >= 0) && (sa.substring(idx) === sb.substring(idx)) && (rx <= base)) {
		base = parseInt(sa.charAt(--idx), rx);
		base = isNaN(base) ? 100000 : base;
	}
	idx++;
	return idx;
}

function compare(sa, sb, rx) {
	if (sa === sb) {
		return 0;
	}
	var prefixidx = findPrefix(sa, sb);
	var suffixidx = findSuffix(sa, sb, rx);
	var saa = sa.substring(prefixidx, suffixidx);
	var sbb = sb.substring(prefixidx, suffixidx);
	return parseInt(saa, rx) - parseInt(sbb, rx);
}

function SerialRange(beginS, EndS, radix) {
	// beginning serial, ending serial, radix, and count
	this.beginning = beginS;
	this.ending = EndS;
	this.radix = (typeof(radix) === 'undefined') ? 10 : radix;
	return this;
}

SerialRange.prototype.count = function() {
	return Math.abs(compare(this.beginning, this.ending, this.radix))+1;
}

SerialRange.prototype.reset = function() {
	this.ptr = this.beginning;
}

SerialRange.prototype.next = function() {
	var output = this.ptr;
	
	if (output == null) {
		this.ptr = null;
	} else if (this.ptr === this.ending) {
		this.ptr = null;
	} else {
		var prefixidx = findPrefix(this.beginning, this.ending);
		var suffixidx = findSuffix(this.beginning, this.ending, this.radix);
		var saa = this.ptr.substring(prefixidx, suffixidx);
		var minlen = saa.length;
		var num = parseInt(saa, this.radix) + 1;
		var ssaa = num.toString(this.radix);
		while (ssaa.length < minlen) {
			ssaa = "0" + ssaa;
		}
		this.ptr = ((prefixidx == -1) ? "" : this.beginning.substring(0, prefixidx)) + ssaa + ((suffixidx == -1) ? "" : this.beginning.substring(suffixidx));
	}
	return output;
}
