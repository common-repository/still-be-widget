/*************
 * [ Still BE ] Insta Widget Lazy Load function
 *************/



"use strict";



// IE用にtemplateのfragmentを取得できるようにする
// Add template fragment for IE
if(document.documentMode){
	Object.defineProperty(HTMLUnknownElement.prototype, "content", {
		get: function(){
			if(this.tagName === "TEMPLATE"){
				if(!this.__content__){
					var fragment = document.createDocumentFragment();
					while(this.firstChild) fragment.appendChild(this.firstChild);
					this.__content__ = fragment;
				}
				return this.__content__;
			} else{
				throw new Error("Not supported.")
			}
		}
	});
}



// HTMLパースが完了したら実行
// Run after HTML parse
window.addEventListener("DOMContentLoaded", function(){
	var callback = function($entries, $observer){
		$entries.forEach(function($entry){
			if(!$entry.isIntersecting) return;
			var template = $entry.target.querySelector(".stillbe-load-later");
			$entry.target.insertBefore(document.importNode(template.content, true), template);
			observer.unobserve($entry.target);
		});
	};
	var options  = {
		root       : null,
		rootMargin : "160px",
		threshold  : 0,
	};
	var observer = new IntersectionObserver(callback, options);
	var temp = Array.prototype.map.call(document.getElementsByClassName("stillbe-load-later"), function($s){
		var flag  = $s.getAttribute("data-lazy-flag");
		var pNode = $s.parentNode;
		if(flag === "on"){
			return pNode;
		}
		$s.setAttribute("data-lazy-flag", "on");
		observer.observe(pNode);
		return pNode;
	});
}, false);
