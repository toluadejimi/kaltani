<!DOCTYPE html>

<html>
<head>
<title></title>
<meta charset="utf-8"/>
<meta content="width=device-width" name="viewport"/>
<style>
		.bee-row,
		.bee-row-content {
			position: relative
		}

		.bee-row-1 .bee-col-1,
		.bee-row-2 .bee-col-1,
		.bee-row-3 .bee-col-1,
		.bee-row-3 .bee-col-2,
		.bee-row-4 .bee-col-1 {
			padding-bottom: 5px;
			padding-top: 5px
		}

		.bee-row-1,
		.bee-row-2,
		.bee-row-3,
		.bee-row-4,
		.bee-row-4 .bee-row-content {
			background-repeat: no-repeat
		}

		body {
			background-color: #fff;
			color: #000;
			font-family: Arial, Helvetica Neue, Helvetica, sans-serif
		}

		.bee-row-3 .bee-col-1 .bee-block-1 a,
		a {
			color: #0068a5
		}

		* {
			box-sizing: border-box
		}

		body,
		p {
			margin: 0
		}

		.bee-row-content {
			max-width: 1170px;
			margin: 0 auto;
			display: flex
		}

		.bee-row-content .bee-col-w1 {
			flex-basis: 8%
		}

		.bee-row-content .bee-col-w5 {
			flex-basis: 42%
		}

		.bee-row-content .bee-col-w6 {
			flex-basis: 50%
		}

		.bee-row-content .bee-col-w12 {
			flex-basis: 100%
		}

		.bee-icon .bee-icon-label-bottom {
			text-align: center
		}

		.bee-icon .bee-icon-label-bottom a,
		.bee-icon .bee-icon-label-right a {
			text-decoration: none
		}

		.bee-image {
			overflow: auto
		}

		.bee-image .bee-center {
			margin: 0 auto
		}

		.bee-row-1 .bee-col-1 .bee-block-1 {
			width: 100%
		}

		.bee-icon {
			display: inline-block;
			vertical-align: middle
		}

		.bee-icon .bee-content {
			display: flex;
			align-items: center
		}

		.bee-image img {
			display: block;
			width: 100%
		}

		.bee-social .icon img {
			max-height: 32px
		}

		.bee-paragraph {
			overflow-wrap: anywhere
		}

		.bee-row-1 .bee-row-content,
		.bee-row-2 .bee-row-content,
		.bee-row-3 .bee-row-content {
			color: #000;
			background-repeat: no-repeat;
			border-radius: 0
		}

		.bee-row-1 .bee-col-1 {
			background-color: #060b39
		}

		@media (max-width:1190px) {
			.bee-row-3 .bee-col-1 {
				padding: 0 0 5px !important
			}
		}

		.bee-row-3 .bee-col-1 .bee-block-1 {
			padding: 10px
		}

		.bee-row-3 .bee-col-1 .bee-block-2,
		.bee-row-3 .bee-col-1 .bee-block-3 {
			color: #000;
			text-align: center;
			font-family: Georgia, Times, 'Times New Roman', serif;
			font-size: 14px;
			letter-spacing: 1px
		}

		.bee-row-3 .bee-col-1 .bee-block-4 {
			text-align: center;
			padding: 10px
		}

		.bee-row-3 .bee-col-3,
		.bee-row-4 .bee-col-1 .bee-block-1 {
			padding-bottom: 5px;
			padding-top: 5px
		}

		.bee-row-4 .bee-row-content {
			color: #000
		}

		.bee-row-4 .bee-col-1 .bee-block-1 {
			color: #9d9d9d;
			font-family: inherit;
			font-size: 15px;
			text-align: center
		}

		.bee-row-4 .bee-col-1 .bee-block-1 .bee-icon-image {
			padding: 5px 6px 5px 5px
		}

		.bee-row-4 .bee-col-1 .bee-block-1 .bee-icon:not(.bee-icon-first) .bee-content {
			margin-left: 0
		}

		.bee-row-4 .bee-col-1 .bee-block-1 .bee-icon::not(.bee-icon-last) .bee-content {
			margin-right: 0
		}

		.bee-row-3 .bee-col-1 .bee-block-2 .bee-icon-image,
		.bee-row-3 .bee-col-1 .bee-block-3 .bee-icon-image {
			padding: 15px
		}

		.bee-row-3 .bee-col-1 .bee-block-2 .bee-icon:not(.bee-icon-first) .bee-content,
		.bee-row-3 .bee-col-1 .bee-block-3 .bee-icon:not(.bee-icon-first) .bee-content {
			margin-left: 2.5px
		}

		.bee-row-3 .bee-col-1 .bee-block-3 .bee-icon::not(.bee-icon-last) .bee-content {
			margin-right: 2.5px
		}

		.bee-row-3 .bee-col-1 .bee-block-2 .bee-icon::not(.bee-icon-last) .bee-content {
			margin-right: 2.5px
		}

		.bee-row-3 .bee-col-1 .bee-block-1 {
			color: #000;
			font-size: 14px;
			font-weight: 400;
			line-height: 120%;
			text-align: left;
			direction: ltr;
			letter-spacing: 0
		}

		.bee-row-3 .bee-col-1 .bee-block-1 p:not(:last-child) {
			margin-bottom: 16px
		}

		@media (max-width:768px) {
			.bee-row-content:not(.no_stack) {
				display: block
			}

			.bee-row-1 .bee-col-1 .bee-block-1 {
				padding: 0 !important
			}

			.bee-row-1 .bee-col-1 .bee-block-1 img {
				float: none !important;
				margin: 0 auto !important
			}

			.bee-row-3 .bee-col-1 .bee-block-3 {
				padding: 15px !important;
				text-align: center !important
			}

			.bee-row-3 .bee-col-1 .bee-block-2 .bee-icon-label,
			.bee-row-3 .bee-col-1 .bee-block-3 .bee-icon-label {
				font-size: 14px !important;
				text-align: center
			}

			.bee-row-3 .bee-col-1 .bee-block-2 {
				padding: 20px 15px 15px !important;
				text-align: center !important
			}

			.bee-row-3 .bee-col-1 .bee-block-1 {
				padding: 20px !important;
				text-align: center !important
			}

			.bee-row-3 .bee-col-1 .bee-block-4 {
				padding: 40px !important
			}
		}
	</style>
</head>
<body>
<div class="bee-page-container">
<div class="bee-row bee-row-1">
<div class="bee-row-content">
<div class="bee-col bee-col-1 bee-col-w12">
<div class="bee-block bee-block-1 bee-image"><img alt="" class="bee-center bee-fixedwidth" src="https://kaltanimis.com/public/upload/5.png" style="max-width:292px;"/></div>
</div>
</div>
</div>
<div class="bee-row bee-row-2">
<div class="bee-row-content">
<div class="bee-col bee-col-1 bee-col-w12"></div>
</div>
</div>
<div class="bee-row bee-row-3">
<div class="bee-row-content">
<div class="bee-col bee-col-1 bee-col-w6">
<div class="bee-block bee-block-1 bee-paragraph">
<p>Contact us for any issues</p>
<p>our support will be available </p>
<p>Monday  - Friday</p>
<p>9am - 4pm </p>
</div>
<div class="bee-block bee-block-2 bee-icons">
<div class="bee-icon bee-icon-last">
<div class="bee-icon-image"><a href="#" target="_self" title=""><img alt="" height="64px" src="https://kaltanimis.com/public/upload/email.png" width="auto"/></a></div>
<div class="bee-icon-label bee-icon-label-bottom"><a href="mailto:info@kaltani.com" target="_self" title="">info@trashbash.com</a></div>
</div>
</div>
<div class="bee-block bee-block-3 bee-icons">
<div class="bee-icon bee-icon-last">
<div class="bee-icon-image"><a href="#" target="_self" title=""><img alt="" height="64px" src="https://kaltanimis.com/public/upload/teleohone.png" width="auto"/></a></div>
<div class="bee-icon-label bee-icon-label-bottom"><a href="tel:+2347041093833" target="_self" title=""> +234 704 109 3833</a></div>
</div>
</div>
<div class="bee-block bee-block-4 bee-social">
<div class="content"><span class="icon" style="padding:0 2.5px 0 2.5px;"><a href="https://www.facebook.com/Kaltanihq"><img alt="Facebook" src="{{url('')}}/public/images/facebook2x.png" title="Facebook"/></a></span><span class="icon" style="padding:0 2.5px 0 2.5px;"><a href="https://twitter.com/kaltanihq"><img alt="Twitter" src="{{url('')}}/public/images/twitter2x.png" title="Twitter"/></a></span><span class="icon" style="padding:0 2.5px 0 2.5px;"><a href="https://www.instagram.com/kaltanihq/"><img alt="Instagram" src="{{url('')}}/public/images/instagram2x.png" title="Instagram"/></a></span><span class="icon" style="padding:0 2.5px 0 2.5px;"><a href="https://www.linkedin.com/company/34582357/admin/"><img alt="LinkedIn" src="{{url('')}}/public/images/linkedin2x.png" title="LinkedIn"/></a></span></div>
</div>
</div>
<div class="bee-col bee-col-2 bee-col-w5"></div>
<div class="bee-col bee-col-3 bee-col-w1"></div>
</div>
</div>
<div class="bee-row bee-row-4">
<div class="bee-row-content">
<div class="bee-col bee-col-1 bee-col-w12">
<div class="bee-block bee-block-1 bee-icons">
<div class="bee-icon bee-icon-last">
<div class="bee-content">
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
