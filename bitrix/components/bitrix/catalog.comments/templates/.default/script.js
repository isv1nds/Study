;(function(window) {
	if (window.JCCatalogSocnetsComments)
		return;

	JCCatalogSocnetsComments = {

		lastWidth: null,

		setFBWidth: function(width)
		{
			if(JCCatalogSocnetsComments.lastWidth == width)
				return;

			JCCatalogSocnetsComments.lastWidth = width;

			var fbDiv = BX("bx-cat-soc-comments-fb");

			if(fbDiv)
			{
				if(fbDiv.childNodes[0])
					fbDiv = fbDiv.childNodes[0];

				if(fbDiv && fbDiv.childNodes[0] && fbDiv.childNodes[0].childNodes[0])
				{
					var fbIframe = fbDiv.childNodes[0].childNodes[0];

					if(fbIframe)
					{
						var src = fbIframe.getAttribute("src");
						var newSrc = src.replace(/width=(\d+)/ig, "width="+width);

						fbDiv.setAttribute("data-width", width+"px");
						fbDiv.childNodes[0].style.width = width+"px";
						fbIframe.style.width = width+"px";

						fbIframe.setAttribute("src", newSrc);
					}
				}
			}
		},

		onFBResize: function(event)
		{
			var width = JCCatalogSocnetsComments.getWidth();

			if(width > 20)
				JCCatalogSocnetsComments.setFBWidth(width-20);
		},

		getWidth: function()
		{
			var result = 0,
				obj = BX("soc_comments_div");

			if(obj && obj.parentNode && obj.parentNode.parentNode)
			{
				var pos = BX.pos(obj.parentNode.parentNode);
				result = pos.width;
			}

			return result;
		}
	};

})(window);
