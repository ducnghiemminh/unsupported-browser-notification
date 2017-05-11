(function($) {
  "use strict" 
	$(document).ready(function() {
		var $doc = $(document)
          , $body = $('body')
          , $modal;        
        
        // Generate message box
        $modal = '<div id="oe-sb-overlay" style="' + (settings.overlay_bg == 0 ? 'background: rgba(0,0,0,0.8)' : 'background: rgba(255,255,255,0.8)') + '">';
        $modal += '<div class="oe-sb-wrapper">';
        $modal += '<h3>' + settings.msg.title + '</h3>';
        $modal += '<p>' + settings.msg.content + '</p>';
        $modal += '<div class="oe-sb-browser-container clearfix">';
        
        $.each(settings.browser, function(key, obj) {
            $modal += '<div class="oe-sb-browser">';
            $modal += '<a class="oe-sb-browser-icon" href="' + obj.url + '" target="_blank"><img src="' + obj.icon + '"/></a>';
            $modal += '<a href="' + obj.url + '" target="_blank">' + obj.name + '</a>';
            $modal += '<p>' + settings.version_text + ' ' + obj.version + '+</p>';
            $modal += '</div>';    
        });
        
        $modal += '</div>';
        $modal += '</div>';
        $modal += '</div>';   
        $modal += '</div>';
        
        // Append message to body
		$body.append($modal);
	});
})(jQuery);