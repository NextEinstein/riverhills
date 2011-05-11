/**
 * Scripturizer for Javascript.
 *
 * Link scripture references to ESV at Good News Publisher or Bible Gateway.
 * Instead of having a server side solution in Perl or PHP, the Javascript
 * version provides a solution to drop into any web page and then
 * automatically scan through the document and generate links.
 *
 * For more information, see
 *
 *   http://fucoder.com/code/scripturizer-js/
 *
 * @author Scott Yang <scotty@yang.id.au>
 * @version 2.2
 */ 

/*************************************************************************
 * Configuration section - Here is the place where you can tune the behaviour
 * of Scripturizer for Javascript.
 *************************************************************************/

var Scripturizer = {
    /**
     * The document element ID used by Scripturize.doDocument(). If it is
     * empty, or the element cannot be found, then document.body will be used,
     * i.e. the entire document will be passed through scripturizer.
     */
    element: 'content',

    /**
     * Maximum number of DOM text nodes to process before handing the event
     * thread back to GUI and wait for the next round. Smaller value leaders
     * to more responsive UI, but slower to finish parsing.
     */
    max_nodes: 500,

    /**
     * Whether a link will open in a new window. This option does not apply to
     * "esvpopup"
     */
    new_window: false,

    /**
     * Version of Bible to be used. If version is 'esv', link to GNP will be
     * created. If version is 'esvpopup', and 'esvpopup.js' is loaded, then we
     * will use the ESV Popup Reference. Otherwise, link to Bible Gateway will
     * be created.
     */  
    version: 'esvpopup'
};

/*************************************************************************
 * Code section - No need to modify the code below this point.
 *************************************************************************/

Scripturizer.translations = {
    'AMP':     ['45', 'Amplified Bible'],
    'ASV':     ['8"', 'American Standard Version'],
    'CEV':     ['46', 'Contemporary English Version'],
    'DARBY':   ['16', 'Darby Translation'],
    'ESV':     ['47', 'English Standard Version'],
    'HCSB':    ['77', 'Holman Christian Standard Bible'],
    'KJ21':    ['48', '21st Century King James Version'],
    'KJV':     ['9',  'King James Version'],
    'MSG':     ['65', 'The Message'],
    'NASB':    ['49', 'New American Standard Bible'],
    'NIRV':    ['76', 'New International Reader\'s Version'],
    'NIV':     ['31', 'New International Version'],
    'NIV-UK':  ['64', 'New International Version - UK'],
    'NKJV':    ['50', 'New King James Version'],
    'NLT':     ['51', 'New Living Translation'],
    'NLV':     ['74', 'New Life Version'],
    'WE':      ['73', 'Worldwide English (New Testament)'],
    'WNT':     ['53', 'Wycliffe New Testament'],
    'YLT':     ['15', 'Young\'s Literal Translation']
};

/**
 * Scripturize a DOM element.
 */
Scripturizer.doElement = function(elm) {
    var vol = 'I+|1st|2nd|3rd|First|Second|Third|1|2|3';
    var bok = 'Genesis|Gen|Exodus|Exod?|Leviticus|Lev|Levit?|Numbers|'+
        'Nmb|Numb?|Deuteronomy|Deut?|Joshua|Josh?|Judges|Jdg|Judg?|Ruth|Ru|'+
        'Samuel|Sam|Sml|Kings|Kngs?|Kin?|Chronicles|Chr|Chron|Ezra|Ez|'+
        'Nehemiah|Nehem?|Esther|Esth?|Job|Jb|Psalms?|Psa?|Proverbs?|Prov?|'+
        'Ecclesiastes|Eccl?|Songs?ofSolomon|Song?|Songs|Isaiah|Isa|Jeremiah|'+
        'Jer|Jerem|Lamentations|Lam|Lament?|Ezekiel|Ezek?|Daniel|Dan|Hosea|'+
        'Hos|Joel|Jo|Amos|Am|Obadiah|Obad?|Jonah|Jon|Micah|Mic|Nahum|Nah|'+
        'Habakkuk|Hab|Habak|Zephaniah|Zeph|Haggai|Hag|Hagg|Zechariah|Zech?|'+
        'Malachi|Malac?|Mal|Mat{1,2}hew|Mat?|Mark|Mrk|Luke|Lu?k|John|Jhn|Jo|'+
        'Acts?|Ac|Romans|Rom|Corinthians|Cor|Corin|Galatians|Gal|Galat|'+
        'Ephesians|Eph|Ephes|Philippians|Phili?|Colossians|Col|Colos|'+
        'Thessalonians|Thes?|Timothy|Tim|Titus|Tts|Tit|Philemon|Phil?|'+
        'Hebrews|Hebr?|James|Jam|Jms|Peter|Pete?|Jude|Ju|Revelations?|Rev|'+
        'Revel';
    var ver = '\\d+(:\\d+)?(?:\\s?[-&]\\s?\\d+)?';
    var regex = '\\b(?:('+vol+')\\s+)?('+bok+bok.toLowerCase()+')\\s+('+ver+'(?:\\s?,\\s?'+
        ver+')*)\\b';

    regex = new RegExp(regex, "m");

    var textproc = function(node) {
        var match = regex.exec(node.data);
        if (match) {
            var val = match[0];
            var node2 = node.splitText(match.index);
            var node3 = node2.splitText(val.length);
            var anchor = node.ownerDocument.createElement('A');
            anchor.setAttribute('href', '#');
            anchor.onclick = Scripturizer.onclick;
            anchor.onmouseover = Scripturizer.onmouseover;

            node.parentNode.replaceChild(anchor, node2);
            anchor.className = 'scripturized';
            anchor.appendChild(node2);
            return anchor;
        } else {
            return node;
        }
    };

    __traverseDOM(elm.childNodes[0], 1, textproc);
};

/**
 * Scripturize the current document.
 */
Scripturizer.doDocument = function() {
    if ((Scripturizer.element && 
         (e = document.getElementById(Scripturizer.element))) ||
        (e = document.body))
    {
	Scripturizer.doElement(e);
    }
};

/**
 * Initialise the module. It only needs to be done once to create/compile
 * regular expression object.
 */
Scripturizer.init = function() {
    var es = document.getElementsByTagName('script');
    var onload = 1; // Default to onload.
    for (i = 0; i < es.length; i ++) {
        var j, p;
        if ((j = es[i].src.indexOf('scripturizer.js')) >= 0) {
            p = __decodeQS(es[i].src);
            if (p.element)
                Scripturizer.element = p.element;
            if (p.new_window)
                Scripturizer.new_window = p.new_window == '1';
            if (p.version)
                Scripturizer.version = p.version;
            else if (p.onload)
                onload = parseInt(p.onload) || 1;
            break;
        }
    }
    return onload;
};

Scripturizer.onclick = function(ev) {
    ev = ev || window.event;
    var verse = this.childNodes[0].data;

    // Check whether 'ESVPopup' variable has been initialised, i.e.
    // 'esvpopup.js' has been loaded. If not, then we will fall back to
    // external linking to GNP.
    if (Scripturizer.version == 'esvpopup') {
        try {
            ESVPopup;
        } catch (e) {
            Scripturizer.version = 'esv';
        }
    }

    if (Scripturizer.version == 'esvpopup') {
        ESVPopup.onclick(ev, verse);
    } else {
        var link = verse.replace(/ /g, '+');
        link = link.replace(/[,&;]/g, '%2C');
        link = link.replace(/:]/g, '%3A');

        switch (Scripturizer.version) {
            case 'esv':
                link = 'http://www.gnpcb.org/esv/search/?go=Go&q=' + link;
                break;
            default:
                var bgver = Scripturizer.translations[
                    Scripturizer.version.toUpperCase()];
                link = 'http://www.biblegateway.com/passage/index.php?version='+
                    bgver[0]+'&search=' + link;
        }

        if (Scripturizer.new_window)
            window.open(link);
        else
            window.location.href = link;
    }

    return false;
};

Scripturizer.onmouseover = function(ev) {
    var title = this.childNodes[0].data;

    switch (Scripturizer.version) {
        case 'esv':
        case 'esvpopup':
            title += ' - English Standard Version Bible';
            break;
        default:
            var bgver = Scripturizer.translations[
                Scripturizer.version.toUpperCase()];
            title += ' - ' + bgver[1] + ' via Bible Gateway';
    }

    if (Scripturizer.version == 'esvpopup')
        title += ' (pop-up)';
    else if (Scripturizer.new_window)
        title += ' (new window)';

    this.setAttribute('title', title);
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

function __traverseDOM(node, depth, textproc) {
    var skipre = /^(a|script|style|textarea)/i;
    var count = 0;
    while (node && depth > 0) {
        count ++;
        if (count >= Scripturizer.max_nodes) {
            var handler = function() {
                __traverseDOM(node, depth, textproc);
            };
            setTimeout(handler, 50);
            return;
        }

        switch (node.nodeType) {
            case 1: // ELEMENT_NODE
                if (!skipre.test(node.tagName) && node.childNodes.length > 0) {
                    node = node.childNodes[0];
                    depth ++;
                    continue;
                }
                break;
            case 3: // TEXT_NODE
            case 4: // CDATA_SECTION_NODE
                node = textproc(node);
                break;
        }

        if (node.nextSibling) {
            node = node.nextSibling;
        } else {
            while (depth > 0) {
                node = node.parentNode;
                depth --;
                if (node.nextSibling) {
                    node = node.nextSibling;
                    break;
                }
            }
        }
    }
}

var __onload = Scripturizer.init();
if (__onload > 0) {
    if (window.attachEvent) {
        window.attachEvent('onload', Scripturizer.doDocument);
    } else if (window.addEventListener) {
        window.addEventListener('load', Scripturizer.doDocument, false);
    } else {
        __onload = window.onload;
        window.onload = function() {
            Scripturizer.doDocument();
            __onload();
        };
    }
} else if (__onload < 0) {
    Scripturizer.doDocument();
}
