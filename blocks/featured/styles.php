#layout-table.format-page #middle-column div.rounded-featured div.content {
    padding: 0 !important;
    margin: 0 !important;
}

/* -----FEATURED STUFF----- */
#featuredContainer {margin:0; padding: 0; margin-top: 35px; width:100%; position:relative; height:100%; overflow:hidden; border: 1px solid #666; border-bottom:none;}
#featuredContent {overflow:hidden; width:100%; height:100%; position:relative;}
#featuredContent ul, #featuredContent li{margin:0;padding:0;list-style:none; position:relative; overflow:hidden; width:100%; height:290px;}
#featuredContent li h2 a {position: absolute; bottom:0; left: 0; right: 0; line-height: 36px; padding:0; text-align:center; font-size: 12px; color: #fff; background-repeat: repeat-x; background-position: left center; width: 100%; font-weight:bold;}
#featuredContent li h2 a {color:#fff;}
span#prevBtn a,
span#prevBtn a:link,
span#prevBtn a:visited,
span#nextBtn a,
span#nextBtn a:link,
span#nextBtn a:visited {
	margin: 0;
	font-weight:bold;
	cursor: pointer;
	position: absolute;
	font-size:12px;
	padding:8px 15px;
	background: #6a3b14 url('../../theme/rhcc2/images/buttonback.jpg') repeat-x center;
	line-height:20px;
	color:#fff;
	font-weight:bold;
	outline: none;
}
span#prevBtn a {bottom:0px; left:0px; border-right:1px solid #89642D;}
span#nextBtn a {bottom:0px; right:0px;  border-left:1px solid #89642D;}
span#prevBtn a:hover, span#nextBtn a:hover {text-decoration: none; background: #6a3b14 url('../../theme/rhcc2/images/buttonback.jpg') repeat-x top;}
span#prevBtn a:active, span#nextBtn a:active {background: #6a3b14 url('../../theme/rhcc2/images/buttonback.jpg') repeat-x bottom;}

body {
		background:#fff url(../images/bg_body.gif) repeat-x;
		font:80% Trebuchet MS, Arial, Helvetica, Sans-Serif;
		color:#333;
		line-height:180%;
		margin:0;
		padding:0;
		text-align:center;
	}
	h1{
		font-size:180%;
		font-weight:normal;
		margin:0;
		padding:0 20px;
		}
	h2{
		font-size:160%;
		font-weight:normal;
		}
	h3{
		font-size:140%;
		font-weight:normal;
		}
	img{border:none;}
	pre{
		display:block;
		font:12px "Courier New", Courier, monospace;
		padding:10px;
		border:1px solid #bae2f0;
		background:#e3f4f9;
		margin:.5em 0;
		width:674px;
		}

    /* image replacement */
        .graphic, #prevBtn, #nextBtn, #slider1prev, #slider1next{
            margin:0;
            padding:0;
            display:block;
            overflow:hidden;
            text-indent:-8000px;
            }
    /* // image replacement */

	#container{
		margin:0 auto;
		position:relative;
		text-align:left;
		width:696px;
		background:#fff;
		margin-bottom:2em;
		}
	#header{
		height:80px;
		line-height:80px;
		background:#5DC9E1;
		color:#fff;
		}
	#content{
		position:relative;
		}

/* Easy Slider */

	#slider ul, #slider li,
	#slider2 ul, #slider2 li{
		margin:0;
		padding:0;
		list-style:none;
		}
	#slider2{margin-top:1em;}
	#slider li, #slider2 li{
		/*
			define width and height of list item (slide)
			entire slider area will adjust according to the parameters provided here
		*/
		width:696px;
		height:241px;
		overflow:hidden;
		}
	#prevBtn, #nextBtn,
	#slider1next, #slider1prev{
		display:block;
		width:30px;
		height:77px;
		position:absolute;
		left:-30px;
		top:71px;
		z-index:1000;
		}
	#nextBtn, #slider1next{
		left:696px;
		}
	#prevBtn a, #nextBtn a,
	#slider1next a, #slider1prev a{
		display:block;
		position:relative;
		width:30px;
		height:77px;
		background:url(../images/btn_prev.gif) no-repeat 0 0;
		}
	#nextBtn a, #slider1next a{
		background:url(../images/btn_next.gif) no-repeat 0 0;
		}

	/* numeric controls */

	ol#controls{
		margin:1em 0;
		padding:0;
		height:28px;
		}
	ol#controls li{
		margin:0 10px 0 0;
		padding:0;
		float:left;
		list-style:none;
		height:28px;
		line-height:28px;
		}
	ol#controls li a{
		float:left;
		height:28px;
		line-height:28px;
		border:1px solid #ccc;
		background:#DAF3F8;
		color:#555;
		padding:0 10px;
		text-decoration:none;
		}
	ol#controls li.current a{
		background:#5DC9E1;
		color:#fff;
		}
	ol#controls li a:focus, #prevBtn a:focus, #nextBtn a:focus{outline:none;}

/* // Easy Slider */