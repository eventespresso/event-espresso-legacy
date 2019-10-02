		
jQuery(document).ready(function() {

// Add class 'selected' to visual-toggle for email confirmation postbox
// Add class to postbox for further styling hook
jQuery("div.visual-toggle p a.toggleVisual").addClass('selected');
jQuery("div.visual-toggle p a.toggleHTML").click(
		function(){
			jQuery(this).parent("p").children("a.toggleHTML").addClass('selected');
			jQuery(this).closest("div.visual-toggle").next(".postbox").addClass('visHTML');
			jQuery(this).parent("p").children("a.toggleVisual").removeClass('selected');
			});
	jQuery("div.visual-toggle p a.toggleVisual").click(
		function() {
			jQuery(this).parent("p").children("a.toggleHTML").removeClass('selected');
			jQuery(this).closest("div.visual-toggle").next(".postbox").removeClass('visHTML');
			jQuery(this).parent("p").children("a.toggleVisual").addClass('selected');
			});
	// add or remove the mce editor 
	jQuery('a.toggleVisual').click(
		function() {
		var id = jQuery(this).closest('div.visual-toggle').next('div.postbox').children('textarea').attr('id');
		//alert( id );
			tinyMCE.execCommand('mceAddControl', false, id);
		}
	);

	jQuery('a.toggleHTML').click(
		function() {
		var id = jQuery(this).closest('div.visual-toggle').next('div.postbox').children('textarea').attr('id');
			tinyMCE.execCommand('mceRemoveControl', false, id);
		}
	);
});

//Confirm Delete
 	function confirmDelete(){
 if (confirm('Are you sure want to delete?')){
      return true;
    }
    return false;
  }
	  
//Select All
  function selectAll(x) {
for(var i=0,l=x.form.length; i<l; i++)
if(x.form[i].type == 'checkbox' && x.form[i].name != 'sAll')
x.form[i].checked=x.form[i].checked?false:true
}


/*
 * Pluralink - easy multilinking. 
 * http://pluralink.com/
*/

var pluralink = {
    pluralinkOptions: {
        pluralinkOver: false,
        pluralinkOldTitle: "",
        hideInterval: 500,
        is_chrome: navigator.userAgent.toLowerCase().indexOf('chrome') > -1,
        is_safari: navigator.userAgent.toLowerCase().indexOf('safari') > -1,
        is_firefox: navigator.userAgent.toLowerCase().indexOf('firefox') > -1,
        is_opera: navigator.userAgent.toLowerCase().indexOf('opera') > -1,
        is_ie: navigator.userAgent.toLowerCase().indexOf('msie') > -1,
        a: document.createElement('form'),
        pattern_normal: /\|\|/,
        pattern_entity: /\%7C\%7C/,
        interval: 0
    },
    pluralink_open: function(link) {
        pluralink.pluralinkOptions.a.setAttribute('action', link);
        pluralink.pluralinkOptions.a.submit()
    },
    pluralink: function(obj) {
        return false
    },
    pluralink_findPos: function(obj) {
        var curleft = 0;
        var curtop = 0;
        var w = obj.offsetWidth;
        var h = obj.offsetHeight;
        if (typeof(obj.offsetParent) != 'undefined') {
            for (var posX = 0, posY = 0; obj; obj = obj.offsetParent) {
                posX += obj.offsetLeft;
                posY += obj.offsetTop
            }
            curleft = posX;
            curtop = posY
        } else {
            curleft = obj.x;
            curtop = obj.y
        }
        return {
            left: curleft,
            top: curtop,
            height: h,
            width: w
        }
    },
    windowSize: function() {
        var w = 0;
        var h = 0;
        if (!window.innerWidth) {
            if (!(document.documentElement.clientWidth == 0)) {
                w = document.documentElement.clientWidth;
                h = document.documentElement.clientHeight
            } else {
                w = document.body.clientWidth;
                h = document.body.clientHeight
            }
        } else {
            w = window.innerWidth;
            h = window.innerHeight
        }
        return {
            width: w,
            height: h
        }
    },
    getMouseXY: function(e) {
        var posx = 0;
        var posy = 0;
        if (!e) var e = window.event;
        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY
        } else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop
        }
        pluralink.mousex = posx;
        pluralink.mousey = posy
    },
    pluralink_over: function(obj) {
        var href = obj.href.split(pluralink.pluralinkOptions.pattern_normal);
        if (href.length < 2) {
            href = obj.href.split(pluralink.pluralinkOptions.pattern_entity)
        }
        pluralink.pluralinkOptions.pluralinkOldTitle = obj.getAttribute("title");
        obj.setAttribute("title", "");
        if (pluralink.pluralinkOptions.pluralinkOldTitle != null) {
            var titles = pluralink.pluralinkOptions.pluralinkOldTitle.split(/\|\|/)
        }
        var pos = pluralink.pluralink_findPos(obj);
        var div = document.getElementById("pluralink-overlay");
        var content = document.getElementById("pluralink-bg");
        content.innerHTML = "";
        var first = true;
        for (c = 0; c < href.length; c++) {
            if (pluralink.pluralinkOptions.pluralinkOldTitle) {
                var text = "<a href='" + href[c] + "'>" + titles[c] + "</a>"
            } else {
                var text = "<a href='" + href[c] + "'>" + href[c] + "</a>"
            }
            if (first) {
                content.innerHTML = text;
                first = false
            } else {
                content.innerHTML = content.innerHTML + "<br />" + text
            }
        }
        if (div.style.display !== "block") {
            var leftpos = pluralink.mousex - 20;
            var toppos = pluralink.mousey + 5;
            var ws = pluralink.windowSize();
            if ((ws.width - 264) < leftpos) {
                leftpos = (ws.width - 264)
            }
            if (pluralink.pluralinkOptions.is_ie) {
                if (document.body.style.marginTop) {
                    var marg = document.body.style.marginTop
                } else {
                    var marg = 15
                }
                var styletop = pos.top + pos.height + marg;
                div.style.display = "block";
                div.style.position = "absolute";
                div.style.left = leftpos + 'px';
                div.style.top = toppos + 'px';
                div.className = 'pluralink-overlay'
            } else {
                div.setAttribute('style', 'display: block; position: absolute; left: ' + leftpos + 'px; top: ' + toppos + 'px;');
                div.setAttribute('class', 'pluralink-overlay')
            }
        }
        pluralink.pluralinkOptions.pluralinkOver = true
    },
    pluralink_out: function(obj) {
        pluralink.pluralinkOptions.pluralinkOver = false;
        if (pluralink.pluralinkOptions.pluralinkOldTitle != null) {
            obj.setAttribute("title", pluralink.pluralinkOptions.pluralinkOldTitle)
        } else {
            obj.setAttribute("title", "")
        }
    },
    pluralink_hideDiv: function() {
        if (!pluralink.pluralinkOptions.pluralinkOver) {
            var div = document.getElementById("pluralink-overlay");
            if (pluralink.pluralinkOptions.is_ie) {
                div.style.display = "none"
            } else {
                if (div) {
                    div.setAttribute('style', 'display: none;')
                }
            }
        }
    },
    init: function() {
        pluralink.pluralinkOptions.interval = window.setInterval(pluralink.pluralink_hideDiv, pluralink.pluralinkOptions.hideInterval);
        var div = document.createElement('div');
        div.setAttribute('id', 'pluralink-overlay');
        div.setAttribute('style', 'display: none;');
        if (pluralink.pluralinkOptions.is_ie) {
            div.attachEvent('onmouseover', function() {
                pluralink.pluralinkOptions.pluralinkOver = true
            });
            div.attachEvent('onmouseout', function() {
                pluralink.pluralinkOptions.pluralinkOver = false
            })
        } else {
            div.setAttribute('onMouseOver', 'pluralink.pluralinkOptions.pluralinkOver = true;');
            div.setAttribute('onMouseOut', 'pluralink.pluralinkOptions.pluralinkOver = false;')
        }
        var divtop = document.createElement('div');
        divtop.setAttribute('id', 'pluralink-top');
        var divbg = document.createElement('div');
        divbg.setAttribute('id', 'pluralink-bg');
        var divbottom = document.createElement('div');
        divbottom.setAttribute('id', 'pluralink-bottom');
        div.appendChild(divtop);
        div.appendChild(divbg);
        div.appendChild(divbottom);
        document.body.appendChild(div);
        var elements = document.getElementsByTagName("a");
        for (var c = 0; c < elements.length; c++) {
            var el = elements[c];
            var hr = el.href.split(pluralink.pluralinkOptions.pattern_normal);
            if (hr.length < 2) {
                hr = el.href.split(pluralink.pluralinkOptions.pattern_entity)
            }
            if (hr.length > 1) {
                el.href = el.href.replace(pluralink.pluralinkOptions.pattern_entity, '||');
                var innertext = el.innerHTML;
                if (innertext.search(/^\<img /i) == -1) {
                    el.innerHTML = innertext + "<sup style='font-size: 0.7em;'>[" + hr.length + "]</sup>"
                }
                if (pluralink.pluralinkOptions.is_ie) {
                    el.onclick = function() {
                        pluralink.pluralink(this);
                        return false
                    };
                    el.onmouseover = function() {
                        pluralink.getMouseXY(event);
                        pluralink.pluralink_over(this)
                    };
                    el.onmouseout = function() {
                        pluralink.pluralink_out(this)
                    }
                } else {
                    el.setAttribute('onClick', 'pluralink.pluralink(this); return false;');
                    el.setAttribute('onMouseOver', 'pluralink.getMouseXY(event);pluralink.pluralink_over(this);');
                    el.setAttribute('onMouseOut', 'pluralink.pluralink_out(this);')
                }
            }
        }
    }
};

function pluralink_init() {
    if (jQuery == undefined) {
        if (document.addEventListener) {
            window.addEventListener("mousemove", pluralink.getMouseXY, false);
            document.addEventListener("DOMContentLoaded", pluralink.init, false)
        } else if (document.attachEvent) {
            pluralink.pluralinkOptions.is_ie = true;
            document.onmousemove = pluralink.getMouseXY;
            document.attachEvent("onreadystatechange", function() {
                if (document.readyState === "complete") {
                    pluralink.init()
                }
            })
        }
    } else {
        jQuery(document).ready(function() {
            pluralink.init()
        })
    }
}
pluralink_init();

$jaer = jQuery.noConflict();
jQuery(document).ready(function($jaer) {
	
    //This is to switch the emails in the admin question groups display
	$jaer("#show-question-group-self a").click( function() {
        $jaer("#question-group-all").hide();
		$jaer("#question-group-self").show();
        $jaer('#show-question-group-self').addClass('selected');
        $jaer('#show-question-group-all').removeClass('selected');
	});
	$jaer("#show-question-group-all a").click( function() {
        $jaer("#question-group-self").hide();
		$jaer("#question-group-all").show();
        $jaer('#show-question-group-all').addClass('selected');
        $jaer('#show-question-group-self').removeClass('selected');
	});
	$jaer("#registration_start").change( function() {
		if ($jaer("#recurrence_regis_start_date").length > 0){
			$jaer("#recurrence_regis_start_date").val($jaer("#registration_start").val());
		}							
	});
	$jaer("#registration_end").change( function() {
		if ($jaer("#recurrence_regis_end_date").length > 0){
			$jaer("#recurrence_regis_end_date").val($jaer("#registration_end").val());
		}							
	});
	if ($jaer("#recurrence_regis_start_date").length > 0){
		$jaer("#recurrence_regis_start_date").change( function() {
			if ($jaer("#registration_start").length > 0){
				$jaer("#registration_start").val($jaer("#recurrence_regis_start_date").val());
			}							
		});
	}
	if ($jaer("#recurrence_regis_end_date").length > 0){
		$jaer("#recurrence_regis_end_date").change( function() {
			if ($jaer("#registration_end").length > 0){
				$jaer("#registration_end").val($jaer("#recurrence_regis_end_date").val());
			}							
		});
	}

});

jQuery(document).ready( function($) {
	//these may have ajaxContent
	var espressoAjaxContent = [
		'espresso_news_box_blog'
	];

	espressoAjaxPopulate = function(el) {
		function show(i, id) {
			var p, e = $('#' + id + ' div.inside:visible').find('.widget-loading');
			if ( e.length ) {
				p = e.parent();
				var u = $('#' + id + '_url').text();
				setTimeout( function(){
					p.load( ajaxurl + '?action=espresso-ajax-content&contentid=' + id + '&contenturl=' + u, '', function() {
						p.hide().slideDown('normal', function(){
							$(this).css('display', '');
						});
					});
				}, i * 500 );
			}
		}

		if ( el ) {
			el = el.toString();
			if ( $.inArray(el, espressoAjaxContent) != -1 )
				show(0, el);
		} else {
			$.each( espressoAjaxContent, show );
		}
	};
	espressoAjaxPopulate();
});

				
				