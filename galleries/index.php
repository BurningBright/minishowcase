<?php

	/* 
	 * FILEBROWSER UTILITY
	 * (USE AT YOUR OWN RISK)
	 */
	
	//// FILEBROSWER IS ACTIVE? ///
	$filebrowser_active = false;
	
	/* 
	 * THUMBNAIL CREATION SCRIPT
	 * (USE AT YOUR OWN RISK)
	 */
	
	//// THUMBNAIL CREATION IS ACTIVE? ////
	/* set to 'true' to activate thumbnail creation
	 * and add '/galleries/?thumbnails' to the path
	 * to enter create thumbnails mode */
	$thumbnail_creation = true;
	
	
	
	
	#### DO NOT CHANGE BELOW HERE ###################################
	#### UNLESS YOU KNOW WHAT YOU'RE DOING, OF COURSE ###############
	
	
	//// CREATE THUMBNAILS ////
	if ($thumbnail_creation && isset($_GET['thumbnails'])) {
			
		## import init file
		require_once("../libraries/general.bootstrap.php");
		
		$s = $settings;
		
		if (!is_writable('../cache/')) {
			$writable = @chmod('../cache/', 0777);
			if (!$writable) die('<html><head><title>minishowcase | Private Directory</title><style>p{padding:10px;color:#fff;background:#f60;}a{padding:10px;color:#fc0;}a:hover{color:#fff;}</style></head><body><p><a href="javascript:history.go(-1);">&#171; back</a> minishowcase says: <b>/cache/ folder is not writable so thumbnails cannot be created until it is writable</b></p></body></html>');
		}
?>
<style>
	.red { color: #f30; }
</style>
<html>
<head>
	<title>minishowcase | thumbnailer</title>
	
	<!-- START CPAINT:JSRS ( http://cpaint.sourceforge.net/ ) -->
	<script type="text/javascript" src="../libraries/cpaint2.inc.compressed.js"></script>
	<!-- END CPAINT:JSRS -->
	
	<!-- START FUNCTIONS -->
	<script type="text/javascript">
	
		//// cpaint object (text output)
		var cp = new cpaint()
		cp.set_transfer_mode('get')
		cp.set_response_type('text')
		//if (javascript_debug_flag) cp.set_debug(2)
		
		var galleries = new Array()
		
		var thumbs = new Array()
		
		var thumbLoader = new Image()
		var imageLoader = new Image()
		
		var cache_image_size = <?php echo printValue($settings['cache_image_size'])."\n"?>
		var cache_thumb_size = <?php echo printValue($s['cache_thumb_size'])."\n"?>
		var thumbnail_max_size = <?php echo printValue($thumbnail_max_size)."\n"?>
		var thumbnail_prefix = <?php echo printValue($s['thumbnail_prefix'])."\n"?>
		var square_thumbnails = <?php echo printValue($s['square_thumbnails'])."\n"?>
	
		var gallery_prefix = <?php echo printValue($settings['gallery_prefix'])."\n"?>;
		var image_prefix = <?php echo printValue($settings['image_prefix'])."\n"?>;
		var thumbnail_prefix = <?php echo printValue($settings['thumbnail_prefix'])."\n"?>;
		var large_image_quality = <?php echo printValue($settings['large_image_quality'])."\n"?>
		
		var set_double_encoding = <?php echo printValue($s["set_double_encoding"])?>
		
		var active_id = undefined
		var active_gallery_num = undefined
		
		var real_thumb_size = false
		if (real_thumb_size) cache_thumb_size = thumbnail_max_size
		
		var th_size = cache_thumb_size
		
		function setThumbGalleries()
		{
			thumbs = new Array()
			
			//getID('th').style.width = th_size+'px'
			getID('th').style.height = th_size+'px'
			
			innerhtml('t', 'Thumbnails ('+th_size+'px) | Images ('+cache_image_size+'px)')
			
			innerhtml('s','loading galleries...')
			
			innerhtml('ts', 'Thumbnail:')
			innerhtml('is', 'Image:')
			
			cp.call( '../libraries/ajax.gateway.php',
				'get_galleries',
				updateThumbGalleries );
		}
		
		function updateThumbGalleries(request)
		{
			var result = unescape(request);
			
			if (result != "null") {
			
				var garray = result.split("|");
				
				var gc = '<ul>'
				for (var i=0; i<garray.length-1; i++)
				{
					galleries[i] = new Array()
					var gdata = garray[i].split(":");
					galleries[i]['name'] = gdata[0];
					galleries[i]['images'] = gdata[1];
					
					gc += '<li id="gallery_'+i+'">'
						+ '<span id="gallery_link_'+i+'"><a href="javascript:getGalleryImages(\''+i+'\');">'
						+ galleries[i]['name']
						+ '</a></span><br /><small>('+galleries[i]['images']+' images)</small>'
						+ '</li>'
				}
				gc += '<ul>'
				
				innerhtml('g', gc)
				innerhtml('s','')
			}
		}
		
		function getGalleryImages(num)
		{
			
			var min = 0;
			var max = Number(galleries[num]['images'])
			var id = galleries[num]['name']
			
			innerhtml('t', '<span class="red">CACHING <b>'+max+'</b> THUMBNAILS & IMAGES</span> <b>[DO NOT INTERRUPT]</b>:')
			
			active_id = id
			active_gallery_num = num
		
			innerhtml('s','creating thumbs for <b>'+galleries[num]['name']+'</b>');
			
			cp.call('../libraries/ajax.gateway.php', 'get_thumbs', 
				updateThumbs, Url.encode(id), min, max);
		}
		
		function updateThumbs(request)
		{
			var result = Url.decode(request)
			var rarray = result.split('|')
			var countNum = rarray.pop();
			var cont = ''
			
			for (i=0; i<rarray.length; i++) {
				var tharray = rarray[i].split(';')
				
				thumbs[i] = new Array()
				thumbs[i]['gallery'] = active_id
				thumbs[i]['image'] = tharray[0]
				thumbs[i]['name'] = tharray[1]
				thumbs[i]['w'] = tharray[2]
				thumbs[i]['h'] = tharray[3]
			}
			
			thNum = 0;
			cacheThumbnail()
		}
		
		function cacheThumbnail()
		{
			if (thNum < thumbs.length) {
				
				var gImg = Number(galleries[active_gallery_num]['images'])
				innerhtml('t', '<span class="red">CACHING <b>'+(gImg-thNum)+'</b> THUMBNAILS & IMAGES</span> <b>[DO NOT INTERRUPT]</b>:')
				
				var thumbSrc = thumbs[thNum]['image']
				var thumbGallery = thumbs[thNum]['gallery']
				var thumbName = thumbs[thNum]['name']
			
				var thumbPath = '../libraries/thumb.display.php?'
					+ 'img=galleries/'
					+ setUrlEncoding(thumbGallery)
					+ '/' + setUrlEncoding(thumbSrc)
					+ '&max=' + cache_thumb_size
					+ '&u=1'
					+ '&c=1'
				if (square_thumbnails) thumbPath += '&sq=1'
				
				thumbLoader.src = thumbPath
				thumbLoader.onload = function()
				{
					var image = thumbs[thNum]['image']
					var name = thumbs[thNum]['name']
					
					var loadedImage = '<img src="'
						+'../cache/'+gallery_prefix
						+setUrlEncoding(active_id)+'/'+thumbnail_prefix
						+setUrlEncoding(image)
						+'" alt="just loaded" />'
						
					innerhtml('th', loadedImage)
					innerhtml('ts', 'Thumbnail: <b>'+ name +'</b> <span class="red">cached</span>')
					innerhtml('is', 'Image: <b>'+ name +'</b>...')
					
					if (thumbs[thNum]['w'] > cache_image_size
						|| thumbs[thNum]['h'] > cache_image_size) {
						cacheImage()
					} else {
						innerhtml('is', 'Image: <b>'+image+'</b> not cached (smaller than '+cache_image_size+'px)')
						thNum++
						cacheThumbnail()
					}
					
				}
				
			} else {
				innerhtml('t', 'Thumbnails ('+th_size+'px) | Images ('+cache_image_size+'px)')
				innerhtml('ts', '')
				innerhtml('is', '')
				innerhtml('th', '')
				innerhtml('s', 'thumbs for <b>'+active_id+'</b> were created')
				var num = active_gallery_num
				var gdel = getID('gallery_link_'+num)
				gdel.innerHTML = active_id
				active_gallery_num = undefined
				active_id = undefined
				thumbs = []
			}
		}
		
		function cacheImage()
		{
			var imageSrc = thumbs[thNum]['image']
			var imageGallery = thumbs[thNum]['gallery']
			var imageName = thumbs[thNum]['name']
			
			var imagePath = '../libraries/thumb.display.php?'
				+ 'img=galleries/'
				+ setUrlEncoding(imageGallery)
				+ '/' + setUrlEncoding(imageSrc)
				+ '&max=' + cache_image_size
				+ '&q=' + large_image_quality
				+ '&pic=1'
				+ '&c=1'
			
			imageLoader.src = imagePath
			imageLoader.onload = function()
			{
				var image = thumbs[thNum]['image']
				var name = thumbs[thNum]['name']
				
				var loadedImage = '<img src="'
					+'../cache/'+gallery_prefix
					+setUrlEncoding(active_id)+'/'+image_prefix
					+setUrlEncoding(image)
					+'" height="100%" alt="just loaded" />'
					
				innerhtml('th', loadedImage)
				innerhtml('is', 'Image: <b>'+ name + '</b> <span class="red">cached</span>')
				
				thNum++
				
				cacheThumbnail()
			}
		}
	</script>
	<!-- END FUNCTIONS -->
	
	<!-- START HELPER FUNCTIONS -->
	<script type="text/javascript">
	
		/**** change innerHTML ****/
		
		function getID(id)
		{
			return document.getElementById(id)
		}
		
		function innerhtml(id, msg)
		{
			getID(id).innerHTML = msg;
		}
	
	
		/**** set url encoding to single/double ****/
	
		function setUrlEncoding(string)
		{
			var output = '';
			if (set_double_encoding) {
				output = Url.double_encode(string);
			} else {
				output = Url.encode(string);
			}
			return output;
		}
		
		/**** URL encode / decode (http://www.webtoolkit.info) ****/
	
		var Url = {
		
			// public method for double-encoding urls
			double_encode : function (string) {
				dbl = escape( this._utf8_encode( string ) );
				return escape( this._utf8_encode( dbl ) );
			},
		
	
			// public method for url encoding
			encode : function (string) {
				return escape(this._utf8_encode(string));
			},
	
			// public method for url decoding
			decode : function (string) {
				return this._utf8_decode(unescape(string));
			},
	
			// private method for UTF-8 encoding
			_utf8_encode : function (string) {
				string = string.replace(/\r\n/g,"\n");
				var utftext = "";
	
				for (var n = 0; n < string.length; n++) {
					var c = string.charCodeAt(n);
					if (c < 128) {
						utftext += String.fromCharCode(c);
					}
					else if((c > 127) && (c < 2048)) {
						utftext += String.fromCharCode((c >> 6) | 192);
						utftext += String.fromCharCode((c & 63) | 128);
					}
					else {
						utftext += String.fromCharCode((c >> 12) | 224);
						utftext += String.fromCharCode(((c >> 6) & 63) | 128);
						utftext += String.fromCharCode((c & 63) | 128);
					}
				}
	
				return utftext;
			},
	
			// private method for UTF-8 decoding
			_utf8_decode : function (utftext) {
				var string = "";
				var i = 0;
				var c = c1 = c2 = 0;
	
				while ( i < utftext.length ) {
					c = utftext.charCodeAt(i);
					if (c < 128) {
						string += String.fromCharCode(c);
						i++;
					}
					else if((c > 191) && (c < 224)) {
						c2 = utftext.charCodeAt(i+1);
						string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
						i += 2;
					}
					else {
						c2 = utftext.charCodeAt(i+1);
						c3 = utftext.charCodeAt(i+2);
						string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
						i += 3;
					}
				}
				return string;
			}
		}
	</script>
	<!-- END HELPER FUNCTIONS -->
	
	<!-- START LOCAL STYLES -->
	<style>
		a {
			padding: 2px;
			color: #F60;
		}
		a:hover {
			color: #FFF;
			background: #F60;
		}
		h2 {
			padding: 6px;
			color: #FFF;
			background: #F60;
		}
		table {
			width: 100%;
		}
		td {
			padding: 10px;
			vertical-align: top;
		}
		td.menu,
		ul {
			width: 300px;
		}
		td.thumbs {
			border-left: 2px solid #CCC;
		}
		td.thumbs div#i {
			height: 48px;
			margin-top: 12px;
		}
		td.thumbs div#i p {
			margin: 0;
			padding: 4px;
		}
		.st {
			padding: 6px;
			color: #666;
			background: #CCC;
		}
		#th {
			/*width: <?php echo ($thumbnail_max_size+40)."px"?>;*/
			height: <?php echo ($thumbnail_max_size+40)."px"?>;
			padding: 10px;
			border: 1px solid #999;
			/*background: #EEE;*/
		}
		#th img {
			border: 0px solid #F60;
		}
		.red {
			color: #F00;
		}
	</style>
	<!-- END LOCAL STYLES -->
</head>
<body>
	<h2>minishowcase | thumbnailer</h2>
	<table><tr><td class="menu">
	<p>Galleries: <span id="g">---</span></p>
	</td><td class="thumbs">
		<p id="t">Thumbnails:</p>
		<div id="th"></div>
		<div id="i">
			<p id="ts"></p>
			<p id="is"></p>
		</div>
	</td></tr></table>
	<p class="st"><small>STATUS: <span id="s">---</span></small></p>
	<script>setThumbGalleries()</script>
</body>
</html>
<?php
	//// SOLVE POSSIBLE SECURITY BREACHS (LOGIN?)
	} else if ($filebrowser_active
		&& file_exists("../extensions/filebrowser/filebrowser.php")
		) {
			//// ADD LOGIN !!! ////
			include("../extensions/filebrowser/filebrowser.php");
	
	//// IF NOT ACTIVE, DIE AND TELL ////
	} else {
		die('<html><head><title>minishowcase | Private Directory</title><style>p{padding:10px;color:#fff;background:#f60;}a{padding:10px;color:#fc0;}a:hover{color:#fff;}</style></head><body><p><a href="javascript:history.go(-1);">&#171; back</a> minishowcase says: <b>this directory is private, sorry...</b></p></body></html>');
	}
	
	//// END FILEBROSWER ////
?>