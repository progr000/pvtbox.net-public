(function() {
	
	var BrowserDetect = {
		init: function () {
			this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
			this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
			this.MyOS = this.searchString(this.dataMyOS)
			this.OS = this.searchString(this.dataOS) || "an unknown OS";
			this.arch = this.searchArch();
		},
		searchString: function (data) {
            //console_log(data);
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1) {
						return data[i].identity;
					}
				}
				else if (dataProp)
					return data[i].identity;
			}
		},
		searchVersion: function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1) return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		},
		searchArch: function() {
			//navigator.userAgent
			var str = navigator.userAgent.toLowerCase();
			if (str.indexOf('x86_64') >= 0) { return '64'; }
			if (str.indexOf('x86-64') >= 0) { return '64'; }
			if (str.indexOf('amd64') >= 0) { return '64'; }
			if (str.indexOf('win64') >= 0) { return '64'; }
			if (str.indexOf('wow64') >= 0) { return '64'; }
			if (str.indexOf('x64_64') >= 0) { return '64'; }
			if (str.indexOf('ia64') >= 0) { return '64'; }
			if (str.indexOf('sparc64') >= 0) { return '64'; }
			if (str.indexOf('ppc64') >= 0) { return '64'; }
			if (str.indexOf('irix64') >= 0) { return '64'; }
			return '32';
		},
		dataBrowser: [
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "edge",
				identity: "edge",
			},
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "chrome",
				identity: "chrome"
			},
			{ 	string: navigator.userAgent.toLowerCase(),
				subString: "omniweb",
				versionSearch: "OmniWeb/",
				identity: "omniweb"
			},
			{
				string: ("vendor" in navigator ? navigator.vendor.toLowerCase() : ""),
				subString: "apple",
				identity: "safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera"
			},
			{
				string: ("vendor" in navigator ? navigator.vendor.toLowerCase() : ""),
				subString: "icab",
				identity: "icab"
			},
			{
				string: ("vendor" in navigator ? navigator.vendor.toLowerCase() : ""),
				subString: "kde",
				identity: "konqueror"
			},
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "firefox",
				identity: "firefox"
			},
			{
				string: ("vendor" in navigator ? navigator.vendor.toLowerCase() : ""),
				subString: "camino",
				identity: "camino"
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent.toLowerCase(),
				subString: "netscape",
				identity: "netscape"
			},
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "msie",
				identity: "msie",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "rv:11",
				identity: "msie",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent.toLowerCase(),
				subString: "trident",
				identity: "msie",
				versionSearch: "MSIE"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent.toLowerCase(),
				subString: "mozilla",
				identity: "netscape",
				versionSearch: "Mozilla"
			}
		],
		dataMyOS : [
			{
				string: navigator.userAgent,
				subString: "Android",
				identity: "Android"
			},
			{
				string: navigator.userAgent,
				subString: "iPhone",
				identity: "iPhone"
			},
			{
				string: navigator.userAgent,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.userAgent,
				subString: "Mac",
				identity: "Mac"
			},
			{
				string: navigator.userAgent,
				subString: "Linux",
				identity: "Linux"
			}
		],
		dataOS : [
            {
                string: navigator.platform,
                subString: "Android",
                identity: "Android"
            },
            {
                string: navigator.userAgent,
                subString: "iPhone",
                identity: "iPhone"
            },
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}
		]
	
	};
	
	BrowserDetect.init();
	
	window.$.client = {
		os : BrowserDetect.MyOS,
		browser : BrowserDetect.browser,
		version : BrowserDetect.version,
		arch : BrowserDetect.arch
	};
	
})();