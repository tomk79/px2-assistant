(function(){
    if( !window.px2style ){
        return;
    }

	window.px2style.registerInitFunction('px2-accordion', function(){
		var accordionModules = document.querySelectorAll('.px2-accordion');
		for(var idx = 0; idx < accordionModules.length; idx ++){

			var units = accordionModules[idx].querySelectorAll(':scope > .px2-accordion__unit');

			for(var idxUnit = 0; idxUnit < units.length; idxUnit ++){
				var a = units[idxUnit].querySelector(':scope > .px2-accordion__label > a');
				var content = units[idxUnit].querySelector(':scope > .px2-accordion__content');
				(function(idxUnit, unit, units, a, content){
					window.px2style.$(a)
						.off('click.px2-accordion')
						.on('click.px2-accordion', function(e){
							if(unit.classList.contains("px2-accordion--active")){
								unit.classList.remove('px2-accordion--active');
							}else{
								unit.classList.add('px2-accordion--active');
							}
						});
				})(idxUnit, units[idxUnit], units, a, content);
			}
		}
	});
})();
