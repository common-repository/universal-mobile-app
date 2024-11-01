var i_w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
var i_h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);

// Old screen sizes. In order to detect orientation changes properly and also avoid flickering
var i_w_old = 0;
var i_h_old = 0;

var i_img_to_show = 0;
var i_seconds_since_rotate = 0;

// To count the total offers.
var i_total_offers = 0;

var xmlhttp;

// The timer
var o_interval = null;

function uni_plg_r_detectWindowSize() {
	i_w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	i_h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
}

function uni_plg_shuffle(array) {
  var copy = [], n = Object.keys(array).length, i;

  // While there remain elements to uni_plg_shuffle…
  while (n) {

    // Pick a remaining element…
    i = Math.floor(Math.random() * n--);

    // And move it to the new array.
    copy.push(array.splice(i, 1)[0]);
  }

  return copy;
}

/*
  It will return the match part, so "Android", "BlackBerry", "iPhone", "iPad"... or null
 */
function uni_plg_device_isMobile() {

	var isMobile = {
		Android: function() {
			return navigator.userAgent.match(/Android/i);
		},
		BlackBerry: function() {
			return navigator.userAgent.match(/BlackBerry/i);
		},
		iOS: function() {
			return navigator.userAgent.match(/iPhone|iPad|iPod/i);
		},
		Opera: function() {
			return navigator.userAgent.match(/Opera Mini/i);
		},
		Windows: function() {
			return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
		},
		any: function() {
			return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
		}
	};

	return isMobile.any();
}

function uni_plg_device_returnMobileDevice() {

	var osType = '';

    if (navigator.userAgent.match(/Android/i)) {
        osType = 'ANDROID';
    }
    if (navigator.userAgent.match(/iPhone/i)) {
        osType = 'IOS';
    }
    if (navigator.userAgent.match(/iPad/i)) {
        osType = 'IOS';
    }
    if (navigator.userAgent.match(/iPod/i)) {
        osType = 'IOS';
    }
    if (navigator.userAgent.match(/BB10/i) || navigator.userAgent.match(/BlackBerry/i)) {
        osType = 'blackberry';
    }
    if (navigator.userAgent.match(/Windows phone/i) || navigator.userAgent.match(/Windows mobile/i) || navigator.userAgent.match(/Windows Phone/i)) {
        osType = 'WP';
    }
    if (navigator.userAgent.match(/Windows NT/i)) {
        osType = 'WU';
    }

    return osType;
}

function uni_plg_g_isset(o_obj) {
	if (typeof o_obj != 'undefined') {
		return true;
	}

	return false;
}

function g_chooseImageSizeForBanner(st_json_response) {

	var s_banner_img = "";

	// Default size
	if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad320x50"])) {
		s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad320x50"];
	}

	if (i_w > 778) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad768x90"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad768x90"];
		}
	} else if (i_w > 490) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad480x80"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad480x80"];
		}
	} else if (i_w > 310) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad300x75"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad300x75"];
		}
	} else if (i_w > 130) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad120x60"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad120x60"];
		}
	}

	return s_banner_img;
}

/* Returns the width or the height of the Banner image choosen */
function g_getImageSizeForBanner(st_json_response, b_width) {

	var i_img_width  = 0;
    var i_img_height = 0;

	// Default size
	if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad320x50"])) {
		i_img_width  = 320;
        i_img_height = 50;
	}

	if (i_w > 778) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad768x90"])) {
            i_img_width  = 768;
            i_img_height = 90;
		}
	} else if (i_w > 490) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad480x80"])) {
            i_img_width  = 480;
            i_img_height = 80;
		}
	} else if (i_w > 310) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad300x75"])) {
            i_img_width  = 300;
            i_img_height = 75;
		}
	} else if (i_w > 130) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad120x60"])) {
            i_img_width  = 120;
            i_img_height = 60;
		}
	}

    if (b_width == true) {
        return i_img_width;
    }

    return i_img_height;
}

function g_chooseImageSizeForInterstitial(st_json_response) {

	var s_banner_img = "";

	// Default size
	if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad320x300"])) {
		s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad320x300"];
	}

	if (i_w > 330 && i_h > 610) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad300x600"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad300x600"];
		}
	} else if (i_w > 330 && i_h > 310) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad320x300"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad320x300"];
		}
	} else if (i_w > 260 && i_h > 260) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad250x250"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad250x250"];
		}
	} else if (i_w > 190 && i_h > 160) {
		if (uni_plg_g_isset(st_json_response["content"][i_img_to_show]["ads"]["ad180x150"])) {
			s_banner_img = st_json_response["content"][i_img_to_show]["ads"]["ad180x150"];
		}
	}

	return s_banner_img;
}

function g_getBannerUrl(st_json_response) {
	s_url = st_json_response["content"][i_img_to_show]["url"];

	return s_url;
}

function uni_plg_universalInterstitialClose() {
    document.getElementById('universal_fullscreen').style.display='none';

    if (o_interval != null) {
        clearInterval(o_interval);
    }
}

function universalBannerClose() {

    document.getElementById('universalBanner').style.display = 'none';

	document.body.style.marginTop = '0px';
	document.body.style.marginBottom = '0px';

    if (o_interval != null) {
        clearInterval(o_interval);
    }

}

/*
  universalBannerEnableImage function is called when the image finish to download
*/
function universalBannerEnableImage(loc, height){
    var body = document.getElementsByTagName("BODY")[0];
    if (loc == 'top'){
        body.style.marginTop = height + 'px';
    } else {
        body.style.marginBottom = height + 'px';
    }
    var universalBanner = document.getElementById("universalBanner");
    if (universalBanner){
        universalBanner.style.display  = 'inline';
    }
}

function g_drawAppList(st_json_response, applistheader) {
	//class to replace = u_universal_network_applist
	
	var count = Object.keys(st_json_response).length;
	st_json_response = uni_plg_shuffle(st_json_response);
	
	//trovare tutti i div con classe u_universal_network_applist
	var targetDivs = document.getElementsByClassName('u_universal_network_applist');
	//per ogni div inserire dentro il contenuto i nomi delle app
	for (var i = 0; i < targetDivs.length; ++i) 
	{
		var item = targetDivs[i];
		if (count > 1)
			item.innerHTML = "<h4 class='suggest_title'>"+applistheader+"</h4>";
		
		//stampo ogni nome di app
		for (var j = 0; i< count-1; j++)	
		{
			console.log(j);
			console.log(st_json_response[j]);
			var app = "<div class='u_appcontainer flex-child'>";
			app += "<a href='"+st_json_response[j].TrackingUrl+"'>";
			app += "<img class='u_appimg' src='"+st_json_response[j].Icon+"' />";
			app += "<span class='u_apptitle'>"+st_json_response[j].Title+"</span>";
			app += "<img src="+st_json_response[j].TrackingPixelUrl+" class='u_appimgtacking' width=1 height=1 />";
			app += "</a>";
			app += "</div>";
			item.innerHTML += app;
		}
		item.innerHTML += "<div style='float: none; clear: both; width:100%; height:1px;'></div>";
		
		item.style = "display: block";
	}
}

function g_drawBanner(s_target_div, s_display, st_json_response) {

	uni_plg_r_detectWindowSize();

	if (i_w != i_w_old) {
		// First time or orientation change
		i_w_old = i_w;
		i_h_old = i_h;

        var universalBanner  = document.getElementById('universalBanner');
        if (!universalBanner){
           var universalBanner = document.createElement("div");
           universalBanner.id = "universalBanner";
        }

        universalBanner.style.display = 'none';

		var s_image      = g_chooseImageSizeForBanner(st_json_response);
        var i_img_width  = g_getImageSizeForBanner(st_json_response, true);
        var i_img_height = g_getImageSizeForBanner(st_json_response, false);

        var s_html = '';
/*		if (s_image != '') {
			s_html += '<a href="#" onclick="document.getElementById(\'universal_fullscreen\').style.display=\'none\';" class="sb-close-pop"><strong>X</strong></a>';
            s_html += "<a href=\"" + g_getBannerUrl(st_json_response) + "\" target=\"_blank\">";
			s_html += "<img src=\"" + s_image + "\" />" + "</a>";
			//document.getElementById(s_target_div).innerHTML = s_html;
		}*/

        if (s_display == 'BANNER_TOP') {
            universalBanner.style.top     = '0';
            s_html = "<a id='close' style='top:5px;' onclick='universalBannerClose();' href='#' class='sb-close'><strong>X</strong></a>";
            s_html += "<a href='" + g_getBannerUrl(st_json_response)  + "'> <img onload=\"universalBannerEnableImage('top'," + i_img_height  +   ");\"   width=\"" +  i_img_width  + "\" height=\"" + i_img_height + "\" style='display: block;margin-left: auto;margin-right: auto'   src='" + s_image  + "' /></a>";
            universalBanner.innerHTML = s_html;

        } else {
            universalBanner.style.bottom = '0';
            s_html = "<a id='close' style='top:0px;' onclick='universalBannerClose();' href='#' class='sb-close'><strong>X</strong></a>";
            s_html += "<a href='" + g_getBannerUrl(st_json_response)  + "'> <img onload=\"universalBannerEnableImage('bottom'," + i_img_height  +   ");\"   width=" +  i_img_width  + " height=" + i_img_height + " style='display: block;margin-left: auto;margin-right: auto'   src='" + s_image  + "' /></a>";
            universalBanner.innerHTML = s_html;
        }
        document.body.appendChild(universalBanner);

	} else {
		i_seconds_since_rotate++;
		if (i_seconds_since_rotate > 9) {
			i_seconds_since_rotate = 0;
			i_img_to_show++;
			// Force display in the next loop
			i_w_old = 0;

			if (i_img_to_show > (i_total_offers - 1)) {
				i_img_to_show = 0;
			}
		}

	}
}

function closeappbanner()
{
	document.getElementById('app-banner-top').style.display = 'none';
}

function uni_drawInterstitialDownloadApp(url, download_message, icon_url) 
{
	if (url == null || url == '')
		return;

	//uni_plg_r_detectWindowSize();

	var banner = "";
	banner += "<div id='app-banner-top' style='padding: 10px 0 0 10px; position: fixed; top:0; width:100%; background-color:rgba(0, 0, 0, 0.8); z-index: 999999;' class='download-app-badge'>";
	banner += "<a style='display: block; color: #fff!important' href='"+url+"'><div class='download-app-badge-left' style='float: left; width: 20%; margin-right: 3%'><img src='"+icon_url+"' style='max-height: 85px; max-width: 85px; margin: 0px auto 0; width: 100%; height: 100%;' /></div>";
	banner += "<div class='download-app-badge-right' style='text-align: left; float:left; width: 60%'><p style='font-family: Arial; line-height: 115%; color: #fff!important; font-size: 13px; margin-top: 0px; margin-bottom: 10px; '>"+download_message+"</p>";
	banner += "<span class='download-app-button' style='font-size: 14px; text-align:center; padding: 5px 10px; margin: 0 0 8px 0; width: 135px; color: #fff!important; display: block; background-color: #26A65B'>SCARICA L'APP</span>";
	banner += "</div></a>";
	banner += "<div onclick='closeappbanner()' class='download-app-badge-close' style='position:absolute; top:15px; right:10px; text-align: center;'><span style='font-family: Arial; display: block; width: 35px; height: 35px; padding-top:10px; font-size: 20px; border-radius: 40px; background-color: #f1f1f1; color: #000'>X</span></div>";

	banner += "</div>";

	document.body.innerHTML += banner;
}

function g_drawInterstitial(s_target_div, st_json_response) {

	uni_plg_r_detectWindowSize();

	if (i_w != i_w_old) {
		// First time or orientation change
		i_w_old = i_w;
		i_h_old = i_h;

		var s_image = g_chooseImageSizeForInterstitial(st_json_response);

		if (s_image != '') {
            if (i_h < 260) {
                // This fixes the size of the style of the div embedded in the CSS for
                // Mobiles like iPhone4 landscape mode (resolution detected 320x214)
                document.getElementById(s_target_div).style.height = "170px";
                document.getElementById(s_target_div).style.width = "200px";
            }

			var s_html = '<a href="#" onclick="uni_plg_universalInterstitialClose();" class="sb-close-pop"><strong>X</strong></a>' + "\n";
            s_html += "<a href=\"" + g_getBannerUrl(st_json_response) + "\" target=\"_blank\">";
			s_html += "<img src=\"" + s_image + "\"  class=\"universal_interstitial\" onload=\"document.getElementById('universal_fullscreen').style.display='inline';\" />" + "</a>";
			document.getElementById(s_target_div).innerHTML = s_html;
		}
	} else {
		i_seconds_since_rotate++;
		if (i_seconds_since_rotate > 14) {
			i_seconds_since_rotate = 0;
			i_img_to_show++;
			// Force display in the next loop
			i_w_old = 0;

			if (i_img_to_show > (i_total_offers - 1)) {
				i_img_to_show = 0;
			}
		}

	}
}

function uni_showDownloadBlogApp(google_play_link, apple_store_link, windows_store_link, download_message, icon_url)
{
	console.log('uni_showDownloadBlogApp');
	var s_device = uni_plg_device_returnMobileDevice();
    if (s_device == '' || s_device == null) {
        s_device = 'ANDROID'; //fallback to most common device
    }

	switch (s_device) {
		case "ANDROID":
			uni_drawInterstitialDownloadApp(google_play_link,download_message,icon_url);
			break;
		case "IOS":
			uni_drawInterstitialDownloadApp(apple_store_link,download_message,icon_url);
		case "WP":
			uni_drawInterstitialDownloadApp(windows_store_link,download_message,icon_url);
		case "WU":
			//uni_drawInterstitialDownloadApp(google_play_link);
		default:
			break;
	}
}

function uni_plg_doAjaxRequest(s_version, s_apikey, s_lang, s_uni_source, s_uni_tool, s_target_div, b_interstitial, applistheader) {

	if (window.XMLHttpRequest) {
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {
		// code for IE6, IE5
		//xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		// Unsupported browser
	}

	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var s_json_response = xmlhttp.responseText;

			var st_json_response = JSON.parse(s_json_response);

			// We cannot use direct .length for associate arrays
			i_total_offers = Object.keys(st_json_response).length;

            if (i_total_offers > 0) //network has served ads
			{
                if (b_interstitial == true) 
				{
                    g_drawInterstitial(s_target_div, st_json_response);
                    // To detect orientation change
                    o_interval = setInterval(function(){ g_drawInterstitial(s_target_div, st_json_response); }, 1000);
                } 
				else 
				{
					//g_drawBanner(s_target_div, s_display, st_json_response);
                    g_drawAppList(st_json_response, applistheader);
                    // To detect orientation change
                    //o_interval = setInterval(function(){ g_drawBanner(s_target_div, s_display, st_json_response); }, 1000);
                }

            }
		}
	}

	var s_url_universal_json = "http://network.myappfree.com/api/Banners";

    var s_device = uni_plg_device_returnMobileDevice();
    if (s_device == '' || s_device == null) {
        s_device = 'ANDROID'; //fallback to most common device
    }

	//http://network.myappfree.com/api/Banners?version=v1&key=ad0f8d2cf607f0d3cece9bbcc7e59db4&os=ANDROID&osVersion=web&lang=it&limit=4
	var s_url_call = s_url_universal_json + "?key=" + s_apikey;
	s_url_call += "&version=" + s_version;
	s_url_call += "&os=" + s_device;
	s_url_call += "&osVersion=web"; //do not support different os version now
	s_url_call += "&uni_source=" + s_uni_source;
	s_url_call += "&lang=" + s_lang;
	s_url_call += "&tool=" + s_uni_tool;
	s_url_call += "&incent=false";
	s_url_call += "&limit=6";

	xmlhttp.open("GET", s_url_call, true);
	xmlhttp.setRequestHeader('Accept', 'application/json');
	xmlhttp.send();

}
