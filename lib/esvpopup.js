/**
 * ESV Popup Reference.
 *
 * Use ESV's Javascript services to create an in-document popup window
 * containing the reference to Bible passage. It can be used together with
 * Scripturizer JS (http://fucoder.com/code/scripturizer-js/). 
 *
 * For more information, see:
 *
 *   http://fucoder.com/code/esvpopup/
 *
 * @author Scott Yang <scotty@yang.id.au>
 * @version 1.4
 */ 

var ESVPopup = {
    baseurl: '',
    frame: null,
    height: 200,    // Default height of the popup window
    width: 300      // Default width of the popup window
};

ESVPopup.get_body = function() {
    if (navigator.userAgent.toLowerCase().indexOf('msie') >= 0) {
        return document.documentElement.clientWidth ? document.documentElement :
            document.body;
    } else {
        return (document.doctype && document.doctype.publicId && 
             (document.doctype.publicId.indexOf('XHTML') >= 0)) ?
            document.documentElement : document.body;
    }
};

ESVPopup.show = function(passage, x, y) {
    var agent = navigator.userAgent.toLowerCase();
    var ie = agent.indexOf('msie') >= 0;
    var ie7 = agent.indexOf('msie 7') >= 0;
    var safari = agent.indexOf('safari') >= 0;

    if (!ESVPopup.baseurl) {
        var elms = document.getElementsByTagName('script');

        for (var i = 0; i < elms.length; i++) {
            if (elms[i].src && (elms[i].src.indexOf("esvpopup.js") >= 0)) {
                var src = elms[i].src;
                var qs = __decodeQS(src);
                ESVPopup.baseurl = src.substring(0, src.lastIndexOf('/')+1);
                if (qs.height)
                    ESVPopup.height = qs.height;
                if (qs.width)
                    ESVPopup.width = qs.width;
                break;
            }
        }
    }

    url = ESVPopup.baseurl + 'esvpopup.html?passage='+encodeURI(passage);
    if (!ESVPopup.frame) {
        var frame = null;

        // MSIE does not have a proper DOM2 implementation so <iframe/>
        // created by DOM2 does not work sometimes. Fortunately we can use
        // insertAdjacentHTML()
        if (ie) {
            var html = '<iframe id="ESVPopupFrame" frameborder="0" '+
                'src="'+url+'" style="position:absolute;top:'+y+
                'px;left:'+x+'px;" scrolling="no" marginheight="0" '+
                'marginwidth="0"></iframe>';
            document.body.insertAdjacentHTML('afterbegin', html);
            frame = document.getElementById('ESVPopupFrame');
            frame.attachEvent('onblur', ESVPopup.hide);
            frame.style.filter = 'progid:DXImageTransform.Microsoft.Shadow'+
                '(color=#888888, Direction=135, Strength=5)';
            document.body.attachEvent('onmousedown', ESVPopup.hide);
        } else {
            var frame = document.createElement('IFRAME');
            document.body.appendChild(frame);
            frame.id = 'ESVPopupFrame';
            frame.frameBorder = 0;
            frame.marginHeight = 0;
            frame.marginWidth = 0;
            frame.src = url;

            frame.style.position = 'absolute';

            frame.addEventListener('blur', ESVPopup.hide, false);
            window.addEventListener('mousedown', ESVPopup.hide, false);
        }

        if (!ie || ie7) {
            frame.style.border = '#888 solid 1px';
        }
        frame.style.height = ESVPopup.height+'px';
        frame.style.width = ESVPopup.width+'px';
        
        ESVPopup.frame = frame;
    } else {
        ESVPopup.frame.src = url;
        ESVPopup.frame.style.display = '';
    }

    // Fixing up the position so no overflow is needed.
    var b = ESVPopup.get_body();
    var dh = b.clientHeight;
    var dw = b.clientWidth;
    dh = (ESVPopup.frame.offsetHeight+y) - dh;
    dw = (ESVPopup.frame.offsetWidth+x) - dw;
    if (safari) {
        dh -= document.body.scrollTop;
        dw -= document.body.scrollLeft;
    } else {
        dh -= b.scrollTop;
        dw -= b.scrollLeft;
    }

    ESVPopup.frame.style.left = x-(dw>0?dw:0) + 'px';
    ESVPopup.frame.style.top  = y-(dh>0?dh:0) + 'px';
};

ESVPopup.onclick = function(e, passage) {
    if (!e) e = window.event;

    var x = e.pageX;
    var y = e.pageY;
    if (isNaN(x)) {
        var b = ESVPopup.get_body();
        x = e.clientX + b.scrollLeft;
        y = e.clientY + b.scrollTop;
    }

    if (!passage) {
        passage = e.srcElement || e.target;
        passage = passage ? (passage.innerText || passage.textContent) : '';
    }
    ESVPopup.show(passage, x, y);
};

ESVPopup.hide = function(event) {
    var f = ESVPopup.frame;
    if (f)
        f.style.display = 'none';
};

function __decodeQS(qs) {
    var k, v, i1, i2, r = {};
    i1 = qs.indexOf('?');
    i1 = i1 < 0 ? 0 : i1 + 1;
    while ((i1 >= 0) && ((i2 = qs.indexOf('=', i1)) >= 0)) {
        k = qs.substring(i1, i2);
        i1 = qs.indexOf('&', i2);
        v = i1 < 0 ? qs.substring(i2+1) : qs.substring(i2+1, i1++);
        r[unescape(k)] = unescape(v);
    }
    return r;
}
