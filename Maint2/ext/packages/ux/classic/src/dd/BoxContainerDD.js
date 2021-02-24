/**
 * A DragDrop implementation specialized for use with BoxReorderer.
 */
Ext.define('Ext.ux.dd.BoxContainerDD', {
    extend: 'Ext.dd.DD',

    /**
     * @method alignElWithMouse
     * @member Ext.dd.DD
     * @inheritdoc
     */
    alignElWithMouse: function(el, iPageX, iPageY) {
        var oCoord = this.getTargetCoord(iPageX, iPageY),
            fly = el.dom ? el : Ext.fly(el, '_dd'),
            elSize = fly.getSize(),
            EL = Ext.Element,
            vpSize,
            aCoord;
        
        if (!this.cachedViewportSize) {
            vpSize = this.cachedViewportSize =
               { width: EL.getDocumentWidth(), height: EL.getDocumentHeight() };
        }
        else {
            vpSize = this.cachedViewportSize;
        }
 
        aCoord = [
            Math.max(0, Math.min(oCoord.x, vpSize.width - elSize.width)),
            Math.max(0, Math.min(oCoord.y, vpSize.height - elSize.height))
        ];
        
        fly.setXY(aCoord);

        this.cachePosition(oCoord.x, oCoord.y);
        this.autoScroll(oCoord.x, oCoord.y, el.offsetHeight, el.offsetWidth);

        return oCoord;
    },

    /**
     * @method autoScroll
     * @member Ext.dd.DD
     * @inheritdoc
     */
    autoScroll: function(x, y, h, w) {
        var clientH,
            clientW,
            st,
            sl,
            bot,
            right,
            toBot,
            toRight,
            thresh,
            scrAmt,
            container = this.container;
        
        if (this.scroll) {
            // The client height
            clientH = Ext.Element.getViewportHeight();
            
            // The client width
            clientW = Ext.Element.getViewportWidth();
            
            // The amt scrolled down
            st = this.DDMInstance.getScrollTop();
            
            // The amt scrolled right
            sl = this.DDMInstance.getScrollLeft();
            
            // Location of the bottom of the element
            bot = h + y;
            
            // Location of the right of the element
            right = w + x;
            
            // The distance from the cursor to the bottom of the visible area,
            // adjusted so that we don't scroll if the cursor is beyond the
            // element drag constraints
            toBot = (clientH + st - y - this.deltaY);
            
            // The distance from the cursor to the right of the visible area
            toRight = (clientW + sl - x - this.deltaX);
            
            // How close to the edge the cursor must be before we scroll
            // var thresh = (document.all) ? 100 : 40;
            thresh = 40;
            
            // How many pixels to scroll per autoscroll op.  This helps to reduce
            // clunky scrolling. IE is more sensitive about this ... it needs this
            // value to be higher.
            scrAmt = (document.all) ? 80 : 30;

            // Scroll down if we are near the bottom of the visible page and the
            // obj extends below the crease
            if (bot > clientH && toBot < thresh) {
                container.scrollTo(sl, st + scrAmt);
            }

            // Scroll up if the window is scrolled down and the top of the object
            // goes above the top border
            if (y < st && st > 0 && y - st < thresh) {
                container.scrollTo(sl, st - scrAmt);
            }

            // Scroll right if the obj is beyond the right border and the cursor is
            // near the border.
            if (right > clientW && toRight < thresh) {
                container.scrollTo(sl + scrAmt, st);
            }

            // Scroll left if the window has been scrolled to the right and the obj
            // extends past the left border
            if (x < sl && sl > 0 && x - sl < thresh) {
                container.scrollTo(sl - scrAmt, st);
            }
        }
    }
});
