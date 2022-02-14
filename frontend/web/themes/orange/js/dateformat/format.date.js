var formatDate = function(utc) {

    this.dateFormat = 'd.m.Y H:i';
    this.fancyFormat = null;//'$1 H:i';
    this.utc = utc ? 'UTC' : '';

    this.getHours    = 'get'+this.utc+'Hours';
    this.getMinutes  = 'get'+this.utc+'Minutes';
    this.getSeconds  = 'get'+this.utc+'Seconds';
    this.getDate     = 'get'+this.utc+'Date';
    this.getDay      = 'get'+this.utc+'Day';
    this.getMonth    = 'get'+this.utc+'Month';
    this.getFullYear = 'get'+this.utc+'FullYear';

    var date = new Date();
    this.today = (new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate())).getTime()/1000;
    //if (_GLOBAL.UserTimeZoneOffset == 0) {
        this.today = _GLOBAL.today;// - _GLOBAL.UserTimeZoneOffset;
    //}
    this.yesterday = this.today - 86400;

    this.i18 = {
        Today : 'Today',
        Yesterday : 'Yesterday',
        months : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        monthsShort : ['msJan', 'msFeb', 'msMar', 'msApr', 'msMay', 'msJun', 'msJul', 'msAug', 'msSep', 'msOct', 'msNov', 'msDec'],
        days : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        daysShort : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    };
};

formatDate.prototype.i18n = function(val) {
    //console_log(this.lang);
    //console_log(this.lang);
    if (this.lang && typeof(this.lang) == 'object' && val in this.lang) {
        //console_log(this.lang[val]);
        return this.lang[val];
    } else {
        return val;
    }
};

formatDate.prototype.exec = function(ts, format) {

    var date, output, d, dw, m, y, h, g, i, s, _format;
    var self = this;

    ts = parseInt(ts);

    if (ts > 0) {

        //console_log(ts);
        date = new Date(ts*1000);

        //console_log(this.getHours);
        h  = date[this.getHours]();
        g  = h > 12 ? h - 12 : h;
        i  = date[this.getMinutes]();
        s  = date[this.getSeconds]();
        d  = date[this.getDate]();
        dw = date[this.getDay]();
        m  = date[this.getMonth]() + 1;
        y  = date[this.getFullYear]();

        _format = (ts >= this.yesterday && this.fancyFormat)
            ? this.fancyFormat
            : this.dateFormat;
        //console_log(format);
        if (format) { _format = format; }

        output = _format.replace(/[a-z]/gi, function(val) {
            switch (val) {
                case 'd': return d > 9 ? d : '0'+d;
                case 'j': return d;
                case 'D': return self.i18n(self.i18.daysShort[dw]);
                case 'l': return self.i18n(self.i18.days[dw]);
                case 'm': return m > 9 ? m : '0'+m;
                case 'n': return m;
                case 'M': return self.i18n(self.i18.monthsShort[m-1]);
                case 'F': return self.i18n(self.i18.months[m-1]);
                case 'Y': return y;
                case 'y': return (''+y).substr(2);
                case 'H': return h > 9 ? h : '0'+h;
                case 'G': return h;
                case 'g': return g;
                case 'h': return g > 9 ? g : '0'+g;
                case 'a': return h >= 12 ? 'pm' : 'am';
                case 'A': return h >= 12 ? 'PM' : 'AM';
                case 'i': return i > 9 ? i : '0'+i;
                case 's': return s > 9 ? s : '0'+s;
            }
            return val;
        });


        //console_log(output);
        //console_log(this.yesterday);
        return ts >= this.yesterday
            ? output.replace('$1', ts >= this.today ? self.i18n(self.i18.Today) : self.i18n(self.i18.Yesterday))
            : output;

        //return output;
    }

    return self.i18n('dateUnknown');
}

$(document).ready(function() {
    formDate = new formatDate(true);
    formDate.fancyFormat = _GLOBAL.datetime_fancy_format;
    formDate.dateFormat  = _GLOBAL.datetime_format;
});