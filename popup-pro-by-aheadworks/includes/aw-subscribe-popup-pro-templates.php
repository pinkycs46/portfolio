<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AwSubscribePopupProTemplates {

	public static function aw_get_templates_data( $template) {
		$close_btn = '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 241.171 241.171" style="enable-background:new 0 0 241.171 241.171;" xml:space="preserve">
			<path id="Close" d="M138.138,120.754l99.118-98.576c4.752-4.704,4.752-12.319,0-17.011c-4.74-4.704-12.439-4.704-17.179,0
			l-99.033,98.492L21.095,3.699c-4.74-4.752-12.439-4.752-17.179,0c-4.74,4.764-4.74,12.475,0,17.227l99.876,99.888L3.555,220.497
			c-4.74,4.704-4.74,12.319,0,17.011c4.74,4.704,12.439,4.704,17.179,0l100.152-99.599l99.551,99.563
			c4.74,4.752,12.439,4.752,17.179,0c4.74-4.764,4.74-12.475,0-17.227L138.138,120.754z"/>
		</svg>';

		switch ($template) {
			/*******************Template01 Starts*****************************/
			case 'aw-popup-template01':
				$style = '<style>
			.popup-main.subscribe-one {/*background-color:#fff;*/ max-width:450px; margin:0px auto; overflow:hidden; position: relative; font-family:Arial, Helvetica, sans-serif; 
			border-radius:15px; -moz-border-radius:15px; -webkit-border-radius:15px;}
			.popup-main.subscribe-one .header-title span { line-height:1; position:absolute; top:0; left:20px; text-align: center; color:#fff; padding: 6px 10px; text-transform: uppercase; font-size: 12px; z-index:9;}
			.popup-main.subscribe-one .form-graphic { text-align: center; width: 100%; padding: 0;}
			#graphic-circle img { max-width: 100%; border-radius:3px 3px 0 0;}
			.graphic-circle { max-height: 352px; overflow: hidden;}
			.popup-main.subscribe-one .form-panel ul li::marker {display: none !important; height: 0 !important;}
			.popup-main.subscribe-one .form-panel{ width:100%; /*background-color:#fff;*/ padding:32px 40px 15px 40px; margin-top: 0px; box-sizing: border-box;}
			.popup-main.subscribe-one .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-one .form-panel ul li { margin: 0 0 20px; line-height: 1;}
			.popup-main.subscribe-one .form-panel .input-txt1 { margin:0; display:block; border:none; border-bottom: 1px solid #ccc; width: 100%; height: 35px; padding: 0 10px; box-shadow:none; box-sizing: border-box; background-color: transparent !important;}
			.popup-main.subscribe-one .form-panel ul li.action { text-align: center; margin: 0 0 10px; padding-top: 10px;}
			.popup-main.subscribe-one .form-panel ul li.action .submit-btn { position: relative; width: 140px; height: 35px; border: none; background-color: #000; color: #fff; text-transform: lowercase; padding: 0 30px; cursor: pointer;text-decoration:none;
			border-radius: 100px; -moz-border-radius: 100px; -webkit-border-radius: 100px;}
			.popup_pro_circle_bubble{position:absolute; width:6px; height:6px; border:1px solid #f00; border-radius:50px; display:inline-block;}
			.popup_pro_circle_bubble.one{left:-12px; top:18px; border-color:#7CABF1;}
			.popup_pro_circle_bubble.two{left:-6px; top:34px; border-color:#FC78EE;}
			.popup_pro_circle_bubble.three{right:-9px; top:28px; border-color:#70D3FF;}
			.popup_pro_circle_bubble.four{right:2px; top:38px; border-color:#8B96EF;}
			.popup-main.subscribe-one .form-panel ul li.action .submit-btn:hover { background-color: #48CBDF; color: #fff;}
			.popup-main.subscribe-one .close-pop{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-one .close-pop a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;}
			.popup-main.subscribe-one .close-pop a:before, 
			.popup-main.subscribe-one .close-pop a:after { position: absolute; left: 15px; content: " "; height: 16px; width: 2px; background-color: #333; opacity:0.6;}
			.popup-main.subscribe-one .close-pop:hover a:before, 
			.popup-main.subscribe-one .close-pop:hover a:after { opacity:1;}
			.popup-main.subscribe-one .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-one .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-one .close-pop1 a{color: #000;font-size: 20px;position: absolute;top: 10px;right:16px;text-decoration: none;cursor: pointer;z-index: 99;}
			.popup-main.subscribe-one .close-pop1:hover a:{color: #000;cursor: pointer;}
			h1:not(.site-title):before, h2:before {display: none}
			.subscribe-one.bg_active .form-graphic img { opacity: 0;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}

			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) { 
				.popup-main.subscribe-one { max-height: 275px; overflow-y: scroll;}
				#TB_window{margin-top:0 !important; top:10% !important;}
			}

			@media screen and (min-width: 568px) and (max-width: 767px) {
				.popup-main.subscribe-one { max-height: 275px; overflow-y: scroll;}
			}
			</style>';

				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw_circle_blue.png';

				$body = '<div class="popup">
						<div class="popup-main subscribe-one">
							<div class="close-pop1"><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="header-title"><span class="popup">Follow</span></div>
							<div class="form-graphic">
								<div class="graphic-circle" id="graphic-circle">
									<img src="' . $img . '" id="subscrib_imag_o">
								</div>
							</div>
							<div class="form-panel">
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li>
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
									</li>
									<li class="action">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe">Subscribe</span>
											<em class="popup_pro_circle_bubble one"></em>
											<em class="popup_pro_circle_bubble two"></em>
											<em class="popup_pro_circle_bubble three"></em>
											<em class="popup_pro_circle_bubble four"></em>
										</button>
									</li>
								</ul>
							</div>
							<div id="template_width" style="display:none;">451</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template01 Ends*****************************/


			/*******************Template02 Starts*****************************/
			case 'aw-popup-template02':
				$style = '<style>
			*, *:after { box-sizing: border-box;}
			.clearfix:after { visibility: hidden; display: block; font-size: 0; content: " "; clear: both; height: 0;}
			.popup-main.subscribe-two {background-color:#fff; max-width:713px; margin:0px auto; padding: 35px 15px 19px; font-family:Arial, Helvetica, sans-serif; position:relative; }
			.popup-main.subscribe-two .header-title { float: left; width:50%; padding: 15px 10px 10px 0; font-size: 16px; box-sizing:border-box;}
			.popup-main.subscribe-two .header-title h2 { margin: 0 0 20px; padding:0 0 0 5px; color:#000; position:relative;}
			.popup-main.subscribe-two .header-title p { font-size: 12px; margin: 0px; text-align: left; padding: 15px 0 0 15px !important; max-width: 80%; color:#444;}
			.popup-main.subscribe-two .header-title .popup_pro_two_imgs{ height: 47px; margin: 0 0 30px;}
			.popup-main.subscribe-two .header-title img { max-width: 100%; margin-left: -15px; height: 47px;}
			.popup-main.subscribe-two .form-panel{ float: left; width:50%; padding:35px 4% 25px 0; box-sizing: border-box;}
			.popup-main.subscribe-two .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-two .form-panel ul li { margin: 0 0 30px; line-height: 1;}
			.popup-main.subscribe-two .form-panel .input-txt1 { margin:0; line-height: 1; border: 1px solid #ccc; width: 100%; height: 45px; /*padding: 0px 0px 0px 10px;*/ box-sizing: border-box; text-align: left; background-color: transparent !important;}
			.popup-main.subscribe-two .form-panel ul li.action { text-align: right; margin: 0 0 10px;}
			.popup-main.subscribe-two .form-panel ul li.action .submit-btn { height: 45px; border: none; background-color: #3184FF; color: #fff; font-weight:600; text-transform: none; padding: 0 20px; margin-top: 10px; min-width: 180px; cursor: pointer;text-decoration:none;}
			.popup-main.subscribe-two .form-panel ul li.action .submit-btn:hover { background-color: #000; color: #fff}
			.popup-main.subscribe-two .close-pop{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-two .close-pop a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;}
			.popup-main.subscribe-two .close-pop a:before, 
			.popup-main.subscribe-two .close-pop a:after { position: absolute; left: 15px; content: " "; height: 16px; width: 2px; background-color: #333; opacity:0.6;}
			.popup-main.subscribe-two .close-pop:hover a:before, 
			.popup-main.subscribe-two .close-pop:hover a:after { opacity:1;}
			.popup-main.subscribe-two .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-two .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-two .close-pop1{position:absolute; top:5px; right:12px;}
			.popup-main.subscribe-two .close-pop1 a { color: #000; text-decoration: none; font-size: 18px; /*width: 30px;*/ height: 30px; display: block; padding-top: 7px;cursor: pointer;}
			#TB_ajaxContent { margin-top: 0 !important;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}
			#TB_window{margin-top:0 !important; top:10% !important;}

			@media screen and (max-width: 767px) {
				.popup-main.subscribe-two .form-panel ul li { margin: 0 0 15px;}
				.popup-main.subscribe-two .form-panel ul li.action .submit-btn {margin-top:0;}
			}
			@media screen and (min-width: 568px) and (max-width: 767px) {
				#TB_window{margin-top:0 !important; top:10% !important;}
				.popup-main.subscribe-two .form-panel {padding: 15px 4% 5px 0;}
				.popup-main.subscribe-two { max-height: 275px; overflow-y: scroll;}
			}
			@media screen and (max-width: 580px) {
				.popup-main.subscribe-two { max-width: 400px;}
				.popup-main.subscribe-two .header-title { width: 100%; padding: 15px 0px 10px 0;}
				.popup-main.subscribe-two .form-panel { width: 100%; padding: 35px 4% 25px;}
				.popup-main.subscribe-two .form-panel ul li.action { text-align: center;}
			}
			</style>';

				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw-subscribe-txt.png';

				$body = '<div class="popup" >
						<div class="popup-main subscribe-two clearfix" id="popupfullbackground">
							<div class="close-pop1"><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="header-title">
								<div class="popup_pro_two_imgs">
									<img src="' . $img . '" id="subscrib_imag_t">
								</div>
									<p class="popup">Text admin popup one lorem ipsum popup start and finish!</p>
							</div>
							<div class="form-panel">                	
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li>
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
									</li>
									<li class="action">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe">Subscribe</span>
										</button>
									</li>
								</ul>
							</div>
							<div id="template_width" style="display:none;">713</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template02 Ends*****************************/


			/*******************Template03 Starts*****************************/
			case 'aw-popup-template03':
				$style = '<style>
			.popup-main.subscribe-three {background-color:#fff; max-width:100%; margin:0px auto; border-radius:9px; display: block; position: relative; font-family:Arial, Helvetica, sans-serif;}
			.popup-main.subscribe-three:after {content: ""; display: table;clear: both;}
			.popup-main.subscribe-three .form-left{ float: left; display: inline-block; width: 191px; height:350px; padding: 0px; border-radius: 8px 0 0 8px;overflow: hidden;}
			.popup-main.subscribe-three .form-graphic { text-align: center; width: 100%;}
			.popup-main.subscribe-three .form-graphic .graphic-rss { padding: 0; text-align: left;}
			.popup-main.subscribe-three.bg_active .form-graphic img { opacity: 0;}
			.popup-main.subscribe-three .form-panel{ line-height: 1.4; display:inline-block; width:408px; float: left;/*background-color:#fff;*/ padding:25px 40px 0px 40px; border-radius: 0 6px 6px 0;}
			.popup-main.subscribe-three .form-panel .header-title { text-align: center; padding: 10px 15%; line-height: 18.2px;}
			.popup-main.subscribe-three .form-panel .header-title h2 { margin: 0 0 10px;}
			.popup-main.subscribe-three .form-panel .header-title span.popup { line-height: 1;}
			.popup-main.subscribe-three .form-panel .header-title p { margin: 0 0 10px; color: #666; font-size: 16px; line-height:1.4 !important;}
			.popup-main.subscribe-three .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-three .form-panel ul li { margin: 15px 0 0; line-height: 1;}
			.popup-main.subscribe-three .form-panel .input-txt1 {margin:0; border-radius:0; border: 1px solid #FF53A3; width: 100%; height: 35px; padding: 0 10px; box-sizing: border-box; background-color: transparent !important;}
			.popup-main.subscribe-three .form-panel ul li.action { text-align: right; margin: 40px 0 0;}
			.popup-main.subscribe-three .form-panel ul li.action .submit-btn { height: 30px; border: none; background-color: #FF53A3; color: #fff; text-transform: uppercase; padding: 0 45px 0 20px; cursor: pointer; position: relative; text-decoration:none;}
			.popup-main.subscribe-three .form-panel ul li.action .submit-btn:hover { height: 30px; border: none; background-color: #222; color: #fff}
			/*.popup-main.subscribe-three .form-panel ul li.action .submit-btn::after { content: ""; background: url("' . plugin_dir_url(__DIR__) . 'admin/templates/images/aw-arrow-white.png") no-repeat 50% 50% #FF2C8E; width: 30px; height: 100%; display: inline-block; position: absolute; right: 0; top: 0;}*/
			.popup-main.subscribe-three .form-panel ul li.action .submit-btn .btn-arw { background: url("' . plugin_dir_url(__DIR__) . 'admin/templates/images/aw-arrow-white.png") no-repeat 50% 50%; width: 30px; height: 100%; display: inline-block; position: absolute; right: 0; top: 0; border-left: 1px solid rgba(255,255,255,0.2);}
			.popup-main.subscribe-three .close-pop{position:absolute; top: -24px; right: 30px;}
			.popup-main.subscribe-three .close-pop a { color: #000; text-decoration: none; width: 24px; height: 24px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box; background-color: #fff;}
			.popup-main.subscribe-three .close-pop a:before, 
			.popup-main.subscribe-three .close-pop a:after { position: absolute; left: 12px; content: " "; height: 12px; width: 2px; background-color: #FF53A3;}
			.popup-main.subscribe-three .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-three .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-three .close-pop1{position:absolute; top: -22px; right: 35px; width: 24px; background-color: #fff; border-radius: 4px 4px 0 0; -moz-border-radius: 4px 4px 0 0; -webkit-border-radius: 4px 4px 0 0; text-align:center;}
			.popup-main.subscribe-three .close-pop1 a { color: #000; text-decoration: none; width: 24px; height: 24px; display: inline-block; text-align: center; padding-top: 4px; box-sizing: border-box; cursor: pointer; 
				}
			h1:not(.site-title):before {display:none;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0; overflow: inherit;}
			
			@media only screen and (max-width: 767px) {
				.popup-main.subscribe-three .form-panel ul li.action { margin: 18px 0 0;}
			}
			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) {
				#TB_window{margin-top:0 !important; top:10% !important;}
			}
			@media screen and (min-width: 568px) and (max-width: 767px) {
				.popup-main.subscribe-three .three-oflow { max-height: 275px; overflow-y: scroll;}
				.popup-main.subscribe-three .form-panel { 
					width: calc(100% - 191px);
					-moz-width: calc(100% - 191px);
					-webkit-width: calc(100% - 191px);
				}
			}
			@media only screen and (max-width: 580px) {
				.popup-main.subscribe-three { max-width: 400px;}
				.popup-main.subscribe-three .form-left { width: 100%; height:auto;}
				.popup-main.subscribe-three .form-graphic .graphic-rss { text-align: center;}
				.popup-main.subscribe-three.bg_active .form-graphic img {display: none;}
				.popup-main.subscribe-three .form-panel { width: 100%; padding: 25px 20px 20px;}
				.popup-main.subscribe-three .form-left img { max-height: 240px;}
				.popup-main.subscribe-three .form-panel ul li.action { text-align: center; margin: 20px 0 0;}
			}
			</style>';

				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw_template_3.png';

				$body = '<div class="popup">
						<div class="popup-main subscribe-three">
							<div class="close-pop1"><a onclick="return close_tb(this);" class="popup"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="three-oflow">
							<div class="form-left">
								<div class="form-graphic">
									<div class="graphic-rss">
										<img src="' . $img . '" id="subscrib_imag_o">
									</div>
								</div>
							</div>
						<div class="form-panel">
							<div class="header-title">
								<span class="popup">Subscribe!</span>
								<p class="popup">Lorem Ipsum has been the industrys standard dummy text</p>
							</div>
							<ul>
								<li>
									<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
								</li>
								<li>
									<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
								</li>
								<li class="action">
									<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
									<span id="popup_pro_subscribe">Subscribe</span><strong class="btn-arw"></strong>
								</button>
								</li>
							</ul>
						</div>
						</div>
						<div id="template_width" style="display:none;">600</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template03 Ends*****************************/


			/*******************Template04 Starts*****************************/
			case 'aw-popup-template04':
				$style = '<style>
			*, *:after { box-sizing: border-box;}
			.popup-main.subscribe-four {background-color:#eee; max-width:452px; margin:0px auto; overflow:hidden; position: relative; font-family:Arial, Helvetica, sans-serif; 
			border-radius: 20px 20px 0 0; -moz-border-radius: 20px 20px 0 0; -webkit-border-radius: 20px 20px 0 0;}
			.popup-main.subscribe-four .header-title { color:#000; padding: 60px 30px 20px; font-size: 16px;}
			.popup-main.subscribe-four .header-title h2 { margin: 8px 0 10px; color:#000; width: 48%; display: inline-block; line-height: normal;}
			.popup-main.subscribe-four .header-title span { line-height: 1;}
			.popup-main.subscribe-four .header-title p { line-height: 1.4; float:right;font-size: 12px; margin: 0 0 20px; text-transform: none; display: inline-block; width: 50%; text-align: right; vertical-align: top;}
			.popup-main.subscribe-four .form-graphic { text-align: center; width: 100%; padding: 0px; max-height: 274px;overflow: hidden;}
			.popup-main.subscribe-four .form-graphic img { max-width: 100%;}
			.popup-main.subscribe-four.bg_active .form-graphic img { opacity: 0;}
			.popup-main.subscribe-four .form-panel{ width:100%; padding:0px 30px 15px; box-sizing: border-box;}
			.popup-main.subscribe-four .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-four .form-panel ul li { margin: 0 0 15px; line-height: 1;}
			.popup-main.subscribe-four .form-panel .input-txt1 { margin:0; border: 1px solid #444; width: 100%; height: 32px; padding: 0 10px; box-sizing: border-box; text-align: right; background-color: transparent !important;}
			.popup-main.subscribe-four .form-panel ul li.action { text-align: right; margin: 0 0 10px;}
			.popup-main.subscribe-four .form-panel ul li.action .submit-btn { height: 30px; border: none; background-color: #000; color: #000; font-weight:600; text-transform: uppercase; padding: 0 30px; cursor: pointer; box-shadow: 0 0 12px #ccc; -moz-box-shadow: 0 0 12px #ccc; -webkit-box-shadow: 0 0 12px #ccc;text-decoration:none;}
			.popup-main.subscribe-four .form-panel ul li.action .submit-btn:hover { background-color: #000; color: #fff}
			.popup-main.subscribe-four .close-pop{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-four .close-pop a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;}
			.popup-main.subscribe-four .close-pop a:before, 
			.popup-main.subscribe-four .close-pop a:after { position: absolute; left: 15px; content: " "; height: 16px; width: 2px; background-color: #333; opacity:0.6;}
			.popup-main.subscribe-four .close-pop:hover a:before, 
			.popup-main.subscribe-four .close-pop:hover a:after { opacity:1;}
			.popup-main.subscribe-four .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-four .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-four .close-pop1{position:absolute; top:10px; right:15px;}
			.popup-main.subscribe-four .close-pop1 a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;cursor: pointer;}
			h1:not(.site-title):before, h2:before{display:none;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}
			@media screen and (min-width: 568px) and (max-width: 767px) {
				.popup-main.subscribe-four { max-height: 275px; overflow-y: scroll !important;}
			}
			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) { 
				.popup-main.subscribe-four { max-height: 275px; overflow-y: scroll;}
				#TB_window{margin-top:0 !important; top:10% !important;}
			}
			</style>';

				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw-up-slide.png';

				$body = '<div class="popup">    
						<div class="popup-main subscribe-four">
							 <div class="close-pop1"><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="header-title">
								<span class="popup">Subscribe!</span>
								<p class="popup">Admin text start popup subscribe ready!</p>
							</div>        
							<div class="form-panel">                	
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li>
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
									</li>
									<li class="action">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe">Subscribe</span>
										</button>
									</li>
								</ul>
							</div>
							<div class="form-graphic">
								<div class="graphic-circle">
									<img src="' . $img . '" alt="" id="subscrib_imag_o">
								</div>
							</div>
							<div id="template_width" style="display:none;">452</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template04 Ends*****************************/


			/*******************Template05 Starts*****************************/
			case 'aw-popup-template05':
				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw_template_05_arw.png';
				$style = '<style>
			*, *:after { box-sizing: border-box;}
			.popup-main.subscribe-five {background-color:#fff; max-width:350px; margin:0px auto; padding:20px; position: relative; font-family:Arial, Helvetica, sans-serif; border-radius:8px; -moz-border-radius:8px; -webkit-border-radius:8px;  box-sizing: border-box;}
			.popup-main.subscribe-five .header-title { line-height: 1.4; text-align: center; color:#000; padding: 35px 0 20px; text-transform: uppercase; font-size: 16px;}
			.popup-main.subscribe-five .header-title h2 { margin: 0 0 10px; color:#307CFF;}
			.popup-main.subscribe-five .header-title span { line-height: 1;}
			.popup-main.subscribe-five .header-title p { line-height: 1.4; font-size: 15px; margin: 0 0 20px; text-transform: none;overflow-wrap: break-word;}
			.popup-main.subscribe-five .form-panel{ width:100%; padding:0px 15px; box-sizing: border-box;}
			.popup-main.subscribe-five .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-five .form-panel ul li { margin: 0 0 41px; position:relative;}
			.popup-main.subscribe-five .form-panel .input-txt1 {margin:0; border:none; border: 1px solid #307CFF; width: 100%; height: 45px; padding: 0 10px; box-sizing: border-box; background-color: transparent !important;}
			.popup-main.subscribe-five .form-panel ul li.action .input-txt1{padding-right:60px;}
			.popup-main.subscribe-five .form-panel ul li .submit-btn { position:absolute; right:0; top:0px; text-indent: -9999px; border-radius:0; height: 45px; border: none; background: url(' . $img . ') no-repeat 50% 50% #307CFF !important; color: #fff; padding: 0px; cursor: pointer; width: 50px;text-decoration:none;}
			.popup-main.subscribe-five .form-panel ul li .submit-btn:hover { background-color: #333; color: #fff}
			.popup-main.subscribe-five .close-pop{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-five .close-pop a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;}
			.popup-main.subscribe-five .close-pop a:before, 
			.popup-main.subscribe-five .close-pop a:after { position: absolute; left: 15px; content: " "; height: 16px; width: 2px; background-color: #333; opacity:0.6;}
			.popup-main.subscribe-five .close-pop:hover a:before, 
			.popup-main.subscribe-five .close-pop:hover a:after { opacity:1;}
			.popup-main.subscribe-five .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-five .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-five .close-pop1{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-five .close-pop1 a { color: #000; text-decoration: none; font-size: 18px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;cursor: pointer;}
			h1:not(.site-title):before, h2:before {display:none;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}
			
			@media only screen and (max-width: 767px) {
				.popup-main.subscribe-five .header-title { padding: 25px 0 10px;}
				.popup-main.subscribe-five .form-panel ul li { margin: 0 0 20px;}
			}
			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) { 
				.popup-main.subscribe-five { max-height: 275px; overflow-y: scroll;}
				#TB_window{margin-top:0 !important; top:10% !important;}
			}
			@media screen and (min-width: 581px) and (max-width: 767px) {
				.popup-main.subscribe-five{ max-height: 275px; overflow-y: scroll;}
			}
			</style>';

				$body = '<div class="popup">
						<div class="popup-main subscribe-five" id="popupfullbackground">
							 <div class="close-pop1"><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="header-title">
								<span class="popup">Subscribe</span>
								<p class="popup">Lorem Ipsum is simply dummy text</p>
							</div>
							<div class="form-panel">
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li class="action">
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe" >&nbsp;</span>
										</button>
									</li>
								</ul>
							</div>
							<div id="template_width" style="display:none;">350</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template05 Ends*****************************/


			/*******************Template06 Starts*****************************/
			case 'aw-popup-template06':
				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/';
				$style = '<style>
			*, *:after { box-sizing: border-box;}
			.popup-main.subscribe-six {background-color:#fff; max-width:451px; margin:0px auto; overflow:hidden; position: relative; font-family:Arial, Helvetica, sans-serif; border-radius:10px; -moz-border-radius:10px; -webkit-border-radius:10px; }
			.popup-main.subscribe-six .header-title { min-height: 316px; color:#000; padding: 40px 0px 10px; font-size: 16px; box-sizing:border-box; overflow: hidden; position: relative;}
			.popup-main.subscribe-six .header-title span { line-height: normal; position: relative; margin: 0 0 20px; padding:40px 25px 15px 35px; min-height: 110px; color:#000; width: 50%; display: inline-block;
				background: #ffffff; /* Old browsers */
				background: -moz-linear-gradient(left,  #ffffff 0%, #facdcd 100%); /* FF3.6-15 */
				background: -webkit-linear-gradient(left,  #ffffff 0%,#facdcd 100%); /* Chrome10-25,Safari5.1-6 */
				background: linear-gradient(to right,  #ffffff 0%,#facdcd 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#facdcd",GradientType=1 ); /* IE6-9 */
			}
			.popup-main.subscribe-six .header-title p { position: relative; font-size: 12px; margin: 0px; width: 55%; min-height: 135px; text-align: left; float:right; padding: 8px 15px 40px 20px; font-weight: 600; color:#444;
				background: #ffffff; /* Old browsers */
				background: -moz-linear-gradient(left,  #ffffff 0%, #bfeeff 100%); /* FF3.6-15 */
				background: -webkit-linear-gradient(left,  #ffffff 0%,#bfeeff 100%); /* Chrome10-25,Safari5.1-6 */
				background: linear-gradient(to right,  #ffffff 0%,#bfeeff 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#bfeeff",GradientType=1 ); /* IE6-9 */
			}
			.popup-main.subscribe-six .header-title span::after{ content:""; position:absolute; right:-90px; top:20px; width:35px; height:35px; display:inline-block;
				background: #ffffff; /* Old browsers */
				background: -moz-linear-gradient(left,  #bfeeff 0%, #ffffff 100%); /* FF3.6-15 */
				background: -webkit-linear-gradient(left,  #bfeeff 0%,#ffffff 100%); /* Chrome10-25,Safari5.1-6 */
				background: linear-gradient(to right,  #bfeeff 0%,#ffffff 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#bfeeff", endColorstr="#ffffff",GradientType=1 ); /* IE6-9 */
				border-radius:50px; -moz-border-radius:50px; -webkit-border-radius:50px;	
			}
			.popup-main.subscribe-six .header-title p::before{ content:""; position:absolute; left:-140px; top:50px; width:35px; height:35px; display:inline-block;
				background: #ffffff; /* Old browsers */
				background: -moz-linear-gradient(left,  #ffffff 0%, #facdcd 100%); /* FF3.6-15 */
				background: -webkit-linear-gradient(left,  #ffffff 0%,#facdcd 100%); /* Chrome10-25,Safari5.1-6 */
				background: linear-gradient(to right,  #ffffff 0%,#facdcd 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#facdcd",GradientType=1 ); /* IE6-9 */
				border-radius:50px; -moz-border-radius:50px; -webkit-border-radius:50px;	
			}
			.popup-main.subscribe-six .header-title::after { content: ""; width: 60%; height: 12px; background-color: #000; display: block; position: absolute; bottom: 5px; left:0;}
			.popup-main.subscribe-six .form-panel{ width:100%; padding:50px 10% 30px; box-sizing: border-box;}
			.popup-main.subscribe-six .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-six .form-panel ul li { margin: 0 0 15px; line-height: 1;}
			.popup-main.subscribe-six .form-panel .input-txt1 {margin:0; border: 1px solid #ccc; width: 100%; height: 40px; padding: 0 10px; box-sizing: border-box; text-align: left;box-shadow: none; background-color: transparent !important;}
			.popup-main.subscribe-six .form-panel ul li.action { text-align: center; margin: 0 0 10px;}
			.popup-main.subscribe-six .form-panel ul li.action .submit-btn { background: url(' . $img . 'aw_template_06_arw.png) no-repeat 90% 50% #000; height: 45px; width:70%; border: none; color: #fff; font-weight:600; text-transform: uppercase; padding: 0 50px; cursor: pointer;text-decoration:none; }
			.popup-main.subscribe-six .form-panel ul li.action .submit-btn:hover { background: url(' . $img . 'aw_template_06_arw.png) no-repeat 90% 50% #F8B1B1; color: #fff}
			.popup-main.subscribe-six .close-pop{position:absolute; top:10px; right:10px;}
			.popup-main.subscribe-six .close-pop a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;}
			.popup-main.subscribe-six .close-pop a:before, 
			.popup-main.subscribe-six .close-pop a:after { position: absolute; left: 15px; content: " "; height: 16px; width: 2px; background-color: #333; opacity:0.6;}
			.popup-main.subscribe-six .close-pop:hover a:before, 
			.popup-main.subscribe-six .close-pop:hover a:after { opacity:1;}
			.popup-main.subscribe-six .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-six .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-six .close-pop1{position:absolute; top:10px; right:10px;z-index: 99;}
			.popup-main.subscribe-six .close-pop1 a { color: #000; text-decoration: none; font-size: 18px; width: 30px; height: 30px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box;cursor: pointer;}
			h1:not(.site-title):before, span:before {display:none;}
			.popup-main.subscribe-six.bg_active .header-title::after {display:none;}
			.popup-main.subscribe-six.bg_active .header-title span::after,
			.popup-main.subscribe-six.bg_active .header-title p::before {display:none;}

			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}
			@media screen and (min-width: 568px) and (max-width: 767px) {
				.popup-main.subscribe-six { max-height: 275px; overflow-y: scroll;}
			}
			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) { 
				.popup-main.subscribe-six { max-height: 275px; overflow-y: scroll;}
				#TB_window{margin-top:0 !important; top:10% !important;}
			}
			</style>';

				$body = '<div class="popup">
						<div class="popup-main subscribe-six">
							<div class="close-pop1" ><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="header-title" id="popupfullbackground">
								<span class="popup">Subscribe!</span>
								<p class="popup">Text admin popup one lorem ipsum popup start and finish!</p>
							</div>
							<div class="form-panel">
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li>
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
									</li>
									<li class="action">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe">Subscribe</span>
										</button>
									</li>
								</ul>
							</div>
							<div id="template_width" style="display:none;">451</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template06 Ends*****************************/


			/*******************Template07 Starts*****************************/
			case 'aw-popup-template07':
				$style = '<style>
			.popup-main.subscribe-seven {background-color:#fff; max-width:100%; overflow:hidden; margin:0px auto; border-radius:5px; display: block; position: relative; font-family:Arial, Helvetica, sans-serif;}
			.popup-main.subscribe-seven .form-left{ display: inline-block; width: 404px; height: 540px; /*background-color: #FFFFFF;*/ padding: 0px; float: left; border-radius: 6px 0 0 6px;}
			.popup-main.subscribe-seven .form-graphic { text-align: center; width: 100%;}
			.popup-main.subscribe-seven .form-left img { max-width: 100%; border-radius: 6px 0 0 6px;}
			.popup-main.subscribe-seven.bg_active .form-graphic img { opacity: 0;}
			.popup-main.subscribe-seven .form-panel{ display:inline-block; width:46%; float: left; /*background-color:#fff;*/ padding:60px 40px 20px; border-radius: 0 6px 6px 0;}
			.popup-main.subscribe-seven .form-panel .header-title { text-align: center; padding: 10px 5%; line-height: 1;}
			.popup-main.subscribe-seven .form-panel .header-title h2 { margin: 0 0 10px;}
			.popup-main.subscribe-seven .form-panel .header-title p { margin: 0 0 10px; color: #666; font-size: 16px; line-height: 1;}
			.popup-main.subscribe-seven .form-panel ul { margin: 0; padding: 0; list-style: none;}
			.popup-main.subscribe-seven .form-panel ul li { margin: 15px 0 0;}
			.popup-main.subscribe-seven .form-panel .input-txt1 { margin:0; box-shadow: none; border:none; border-bottom: 1px solid #3FA8F2; color:#117AC4; font-size: 15px; width: 100%; height: 60px; padding: 0px; box-sizing: border-box; background-color: transparent !important;}
			/*.popup-main.subscribe-seven .form-panel .input-txt1::placeholder{ color: #3FA8F2;}
			.popup-main.subscribe-seven .form-panel .input-txt1::-webkit-input-placeholder {color: #3FA8F2; } 
			.popup-main.subscribe-seven .form-panel .input-txt1::-moz-placeholder {color: #3FA8F2; opacity: 1;}
			.popup-main.subscribe-seven .form-panel .input-txt1:-ms-input-placeholder { color: #3FA8F2;}
			.popup-main.subscribe-seven .form-panel .input-txt1:-moz-placeholder {color: #3FA8F2; } */
			.popup-main.subscribe-seven .form-panel ul li.action { text-align: center; margin: 65px 0 0px;}
			.popup-main.subscribe-seven .form-panel ul li.action .submit-btn { height: 40px; width: 90%; font-size: 15px; border: none; background-color: #3FA8F2; color: #fff; text-transform: uppercase; padding: 0 20px; cursor: pointer;text-decoration:none; }
			.popup-main.subscribe-seven .form-panel ul li.action .submit-btn:hover { border: none; background-color: #117AC4; color: #fff;}
			.popup-main.subscribe-seven .close-pop{position:absolute; top: 10px; right: 10px;}
			.popup-main.subscribe-seven .close-pop a { color: #000; text-decoration: none; width: 24px; height: 24px; display: block; text-align: center; padding-top: 7px; box-sizing: border-box; background-color: #fff;}
			.popup-main.subscribe-seven .close-pop a:before, 
			.popup-main.subscribe-seven .close-pop a:after { position: absolute; left: 12px; content: " "; height: 12px; width: 2px; background-color: #444;}
			.popup-main.subscribe-seven .close-pop a:before { transform: rotate(45deg);}
			.popup-main.subscribe-seven .close-pop a:after { transform: rotate(-45deg);}
			.popup-main.subscribe-seven .close-pop1{position:absolute; top: 10px; right: 10px;}
			.popup-main.subscribe-seven .close-pop1 a { color: #000; text-decoration: none; width: 24px; height: 24px; display: block; text-align: center; padding-top: 0px; box-sizing: border-box; /*background-color: #fff;*/cursor: pointer;}
			h1:not(.site-title):before, h2:before{display:none;}
			#TB_window{/*background-color:transparent !important;*/ box-shadow:none !important;}
			#TB_window #TB_ajaxContent { padding: 0;}			
			
			@media screen and (max-width: 767px) {
				.popup-main.subscribe-seven .form-panel .input-txt1 {height:50px;}
				.popup-main.subscribe-seven .form-panel ul li.action { margin: 35px 0 0px;}
			}
			@media screen and (min-width: 568px) and (max-width: 767px) {
				.popup-main.subscribe-seven { max-height: 275px; overflow-y: scroll;}
				.popup-main.subscribe-seven .form-left { width: 50%; height: auto;}
				.popup-main.subscribe-seven .form-panel .header-title { padding: 10px 0;}
				.popup-main.subscribe-seven .form-panel { width: 50%; padding: 30px 30px 20px;}
				.popup-main.subscribe-seven .form-left img { display: block !important;}
			}
			@media screen and (max-width: 567px) {
				.popup-main.subscribe-seven { max-width: 400px; }
				.popup-main.subscribe-seven .form-left { width: 100%; height: auto;}
				.popup-main.subscribe-seven .form-left img { max-height: 240px;}
				.popup-main.subscribe-seven .form-panel { width: 100%; padding: 30px 20px 20px;}				
				
			}
			@media only screen 
			  and (min-device-width: 375px) 
			  and (max-device-width: 823px) 
			  and (-webkit-min-device-pixel-ratio: 3)
			  and (orientation: landscape) { 
				.popup-main.subscribe-seven { max-height: 275px; overflow-y: scroll;}
				#TB_window{margin-top:0 !important; top:10% !important;}
			}
			</style>';

				$img = plugin_dir_url(__DIR__) . 'admin/templates/images/aw-subscribe-img-seven.jpg';

				$body = '<div class="popup">
						<div class="popup-main subscribe-seven">
							<div class="close-pop1"><a onclick="return close_tb(this);"><span class="popup_cls">' . $close_btn . '</span></a></div>
							<div class="form-left">
								<div class="form-graphic">
									<img src="' . $img . '" id="subscrib_imag_o">
								</div>
							</div>
							<div class="form-panel">
								<div class="header-title">
									<span class="popup">Subscribe Now!</span>
									<p class="popup">Lorem Ipsum has been the industrys standard dummy text</p>
								</div>
								<ul>
									<li>
										<input type="text" class="input-txt1" placeholder="Name" name="popup-pro-subscribe-name" id="popup-pro-subscribe-name" value="">
									</li>
									<li>
										<input type="text" class="input-txt1" placeholder="Email" name="popup-pro-subscribe-email" id="popup-pro-subscribe-email" value="">
									</li>
									<li class="action">
										<button class="submit-btn" onclick="return do_popup_pro_subscribe(this);">
											<span id="popup_pro_subscribe">Subscribe</span>
										</button>
									</li>
								</ul>
							</div>
							<div id="template_width" style="display:none;">750</div>
						</div>
					</div>';
				$html = $style . $body;
				break;
			/*******************Template07 Ends*****************************/

			default:
				$html = '';
		}
		return $html;
	}
}

