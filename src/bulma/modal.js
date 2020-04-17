document.showModal = function(modal)
{
    if (!modal.classList.contains("modal"))
        modal = document.getClosestParent(modal, ".modal");
       
    modal.classList.add("is-active");
}
document.hideModal = function(modal, state = false)
{	
    if (!modal.classList.contains("modal"))
        modal = document.getClosestParent(modal, ".modal");

	if (modal.onClose && !modal.onClose(modal, state))
		return false;

    modal.classList.remove("is-active");
    
	if (document.onModalClose)
		document.onModalClose(modal, state);

	return true;
}
document.removeModal  = function(modal, data = null)
{
    if (!modal.classList.contains("modal"))
        modal = document.getClosestParent(modal, ".modal");

	if (modal.onClose && !modal.onClose(modal, state))
		return false;

    if (modal.onRemoval)
        modal.onRemoval(data);
    
    if (document.onModalClose)
        document.onModalClose(modal, data != null);

	modal.remove();
	return true;
}
document.getClosestParent = function(elem, selector) {

	// Element.matches() polyfill
	if (!Element.prototype.matches) {
	    Element.prototype.matches =
	        Element.prototype.matchesSelector ||
	        Element.prototype.mozMatchesSelector ||
	        Element.prototype.msMatchesSelector ||
	        Element.prototype.oMatchesSelector ||
	        Element.prototype.webkitMatchesSelector ||
	        function(s) {
	            var matches = (document || ownerDocument).querySelectorAll(s),
	                i = matches.length;
	            while (--i >= 0 && matches.item(i) !== this) {}
	            return i > -1;
	        };
	}

	// Get the closest matching element
	for ( ; elem && elem !== document; elem = elem.parentNode ) {
		if ( elem.matches( selector ) ) return elem;
	}
	return null;
};