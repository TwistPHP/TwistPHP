const CustomSelectBox = '<div data-name="{{{name}}}" class="customSelectBox select-{{{name}}} {{{required}}}"{{{tabindex}}}>'+
	'<div class="selectedOption">{{{selectedtext}}}</div>'+
	'<div class="selectOptionsContainer" title="Searching"><div class="mobileSearch"><input type="text" name="dummy" value=""></div><div class="selectOptions">{{{options}}}</div></div>'+
	'<input type="hidden" name="{{{name}}}" value="{{{selectedvalue}}}">'+
	'</div>',
	CustomSelectBoxOption = '<div data-value="{{{value}}}" class="{{{selected}}} {{{disabled}}}">{{{text}}}</div>';

import {Placeholders} from './Placeholders';
let blListnersStarted = false;
let blIsMobileAndTablet = false;

/**
 * Custom Select by Dan Walker
 */
export class CustomSelect{

	constructor(){

        let Placeholder = new Placeholders();

		$('select').each(function(){

			let options = '';
			let selectedtext = '';
			let selectedvalue = '';

			$(this).children().each(function(){

				let blSelected = false;
				if($(this).is('[selected]')){
					blSelected = true;
					selectedtext = $(this).html();
					selectedvalue = $(this).attr('value');
				}

				options += Placeholder.build(CustomSelectBoxOption,{
					value: $(this).attr('value'),
					text: $(this).html(),
					selected: ($(this).is('[selected]')) ? 'selected' : '',
					disabled: ($(this).is('[disabled]')) ? 'disabled' : '',
				} );
			});

			let html = Placeholder.build(CustomSelectBox,{
				name: $(this).attr('name'),
				required: ($(this).is('[required]')) ? 'required' : '',
				options: options,
				selectedvalue: selectedvalue,
				selectedtext: selectedtext,
				tabindex: ($(this).attr('tabindex')) ? 'tabindex="'+$(this).attr('tabindex')+'"' : ''
			} );

			$(this).replaceWith($( html ));
		});

        blIsMobileAndTablet = this.isMobileAndTablet();
		this.startListeners();
	}

	isMobile(){
        var check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    }

    isMobileAndTablet(){
        var check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        return check;
    }

	startListeners(){

        if(blListnersStarted === false){

        	console.log('Starting custom select box listeners ...');

            window.addEventListener("keydown", function(e){
                if($('.customSelectBox.active').length){
                    //space and arrow keys
                    if([32, 37, 38, 39, 40].indexOf(e.keyCode) > -1) {
                        e.preventDefault();
                    }
                }
            }, false);

            $(window).click(function () {
                //Hide the select menu
                $('.customSelectBox.active').removeClass('active');
            });

            let currentWord = '';
            let currentLetter = '';
            let currentLetterPosition = 0;
            let currentWordPosition = 0;

            $(document).on('click', '.customSelectBox:not(.active)', function (event) {
                (event.stopPropagation) ? event.stopPropagation() : event.cancelBubble = true;
                $('.customSelectBox.active').removeClass('active');
                $(this).addClass('active');
                $(this).find('.selectOptions').scrollTop($(this).find('.selectOptions').children('.selected')[0].offsetTop - 20);
                if(blIsMobileAndTablet){
                    $(this).find('.mobileSearch').show().find('input').focus();
                }else{
                    $(this).focus();
                }
            }).on('mousedown', '.customSelectBox:not(.active)', function (event) {
                event.preventDefault();
            }).on('focus', '.customSelectBox:not(.active)', function (event) {
                if(!blIsMobileAndTablet){
                    $(this).trigger('click');
                }
            }).on('blur', '.customSelectBox.active', function (event) {
                if(!blIsMobileAndTablet){
                    $('.customSelectBox.active').removeClass('active');
                }
            }).on('click', '.customSelectBox .selectOptions >div.disabled', function (event) {
                (event.stopPropagation) ? event.stopPropagation() : event.cancelBubble = true;
            }).on('click', '.customSelectBox .selectOptions >div:not(.disabled)', function (event) {
                (event.stopPropagation) ? event.stopPropagation() : event.cancelBubble = true;
                $('.customSelectBox.active .selectOptions div').show().removeClass('match');
                currentLetter = currentWord = '';
                currentLetterPosition = currentWordPosition = 0;
                $('.customSelectBox.active .selectOptionsContainer').attr('title',currentWord).removeClass('searching');

                if(blIsMobileAndTablet){
                    $('.customSelectBox.active .selectOptionsContainer').find('.mobileSearch input').val(currentWord);
                }

                $(this).parent().children('div').removeClass('predictiveHover').removeClass('selected');
                let parentDiv = $(this).parent().parent().parent();
                $(parentDiv).removeClass('active').children('.selectedOption').html($(this).html());
                $(parentDiv).children('input').val( $(this).addClass('selected').attr('data-value')).trigger("change");
            }).on('keyup', function(event){

                if($('.customSelectBox.active').length){

                    (event.stopPropagation) ? event.stopPropagation() : event.cancelBubble = true;
                    event.preventDefault();

                    let strCharCode = event.which || event.keyCode || 0;
                    let strChar = String.fromCharCode(event.keyCode);

                    if(strCharCode === 38){
                        //Up Arrow

                        let intPrevItem = 0;

                        if($('.customSelectBox.active .selectOptions div.predictiveHover')){

                            if($('.customSelectBox.active .selectOptions div.match').length){
                                let intTotalItems = $('.customSelectBox.active .selectOptions div').length;
                                intPrevItem = $('.customSelectBox.active .selectOptions div.predictiveHover').index()-1;
                                //console.log(intTotalItems+' '+intNextItem);

                                while(intPrevItem >= 0){
                                    //console.log(intNextItem);
                                    if($('.customSelectBox.active .selectOptions div').eq(intPrevItem).hasClass('match')){
                                        //console.log('Match');
                                        break;
                                    }
                                    intPrevItem--;
                                }
                            }else{
                                intPrevItem = $('.customSelectBox.active .selectOptions div.predictiveHover').index()-1;
                            }

                            //console.log(intPrevItem);
                        }else{
                            intPrevItem = $('.customSelectBox.active .selectOptions div.selected').index()-1;
                        }

                        if(intPrevItem < 0){
                            intPrevItem = $('.customSelectBox.active .selectOptions div').length-1;
                        }

                        let textElement = $('.customSelectBox.active .selectOptions div').eq(intPrevItem);

                        $('.customSelectBox.active .selectOptions').animate({
                            scrollTop: $(textElement).parent().scrollTop() + ($(textElement).offset().top - $(textElement).parent().offset().top)
                        }, 100);

                        $('.customSelectBox.active .selectOptions div').removeClass('predictiveHover');
                        $(textElement).addClass('predictiveHover');

                    }else if(strCharCode === 40){
                        //Down Arrow

                        let intNextItem = 0;

                        if($('.customSelectBox.active .selectOptions div.predictiveHover')){

                            if($('.customSelectBox.active .selectOptions div.match').length){
                                let intTotalItems = $('.customSelectBox.active .selectOptions div').length;
                                intNextItem = $('.customSelectBox.active .selectOptions div.predictiveHover').index()+1;
                                //console.log(intTotalItems+' '+intNextItem);

                                while(intNextItem < intTotalItems){
                                    //console.log(intNextItem);
                                    if($('.customSelectBox.active .selectOptions div').eq(intNextItem).hasClass('match')){
                                        //console.log('Match');
                                        break;
                                    }
                                    intNextItem++;
                                }
                            }else{
                                intNextItem = $('.customSelectBox.active .selectOptions div.predictiveHover').index()+1;
                            }

                            //console.log(intNextItem);

                        }else{
                            intNextItem = $('.customSelectBox.active .selectOptions div.selected').index()+1;
                        }

                        if(intNextItem > $('.customSelectBox.active .selectOptions div').length-1){
                            intNextItem = 0;
                        }

                        let textElement = $('.customSelectBox.active .selectOptions div').eq(intNextItem);

                        $('.customSelectBox.active .selectOptions').animate({
                            scrollTop: $(textElement).parent().scrollTop() + ($(textElement).offset().top - $(textElement).parent().offset().top)
                        }, 100);

                        $('.customSelectBox.active .selectOptions div').removeClass('predictiveHover');
                        $(textElement).addClass('predictiveHover');

                    }else if(strCharCode === 13){
                        //Enter Key
                        currentLetter = '';
                        currentLetterPosition = 0;
                        $('.customSelectBox.active .selectOptions div.predictiveHover').removeClass('predictiveHover').trigger('click');
                    }else{

                        if(strCharCode === 8){
                            currentWord = currentWord.substr(0, currentWord.length-1);
                            currentLetter = currentWord.substr(currentWord.length-1, 1);
                            currentLetterPosition = 0;
                            //Re-show all results ready to rematch
                            $('.customSelectBox.active .selectOptions div').show().removeClass('match');
                        }else{
                            //Build up the search word
                            currentWord += strChar.toLowerCase();

                            if(currentLetter !== strChar.toLowerCase()){
                                currentLetter = strChar.toLowerCase();
                                currentLetterPosition = 0;
                            }
                        }

                        if(currentWord === ''){
                            $('.customSelectBox.active .selectOptionsContainer').attr('title',currentWord).removeClass('searching');
                        }else{
                            $('.customSelectBox.active .selectOptionsContainer').attr('title',currentWord).addClass('searching');
                        }

                        let blFoundNextLetter = false;
                        let blFoundNextWord = false;
                        let intItemPosition = 0;

                        $('.customSelectBox.active .selectOptions div').each(function(){
                            if(!$(this).hasClass('searchBox')) {
                                let itemText = (this.textContent || this.innerText || '').toLowerCase();
                                let itemLetter = itemText[0];

                                if (itemText.substr(0, currentWord.length) === currentWord) {
                                    $(this).addClass('match');
                                    if (blFoundNextWord === false) {
                                        blFoundNextWord = intItemPosition;
                                        console.log('Found Word');
                                    }
                                } else {
                                    $(this).hide().removeClass('match');
                                }

                                // find first letter of element contents
                                if (blFoundNextLetter === false && itemLetter === currentLetter) {

                                    if (intItemPosition > currentLetterPosition) {
                                        blFoundNextLetter = intItemPosition;
                                    }
                                }

                                intItemPosition++;
                            }
                        });

                        if(blFoundNextWord === false){
                            $('.customSelectBox.active .selectOptions div').show().removeClass('match');
                            $('.customSelectBox.active .selectOptions').removeClass('searching');
                        }

                        let highlightItemPosition = false;

                        if(currentWord.length > 1 && blFoundNextWord !== false){
                            //Found a word match
                            highlightItemPosition = currentWordPosition = blFoundNextWord;

                        }else if(blFoundNextLetter !== false){
                            //Found the next letter match
                            highlightItemPosition = currentLetterPosition = blFoundNextLetter;
                        }else{
                            //Rest the finder
                            currentLetterPosition = currentWordPosition = 0;
                            currentWord = '';
                        }

                        if(currentWord.length > 1 && blFoundNextWord === false){
                            currentWord = currentLetter;
                            currentWordPosition = 0;
                        }

                        $('.customSelectBox.active .selectOptionsContainer').attr('title',currentWord);

                        if(blIsMobileAndTablet){
                            $('.customSelectBox.active .selectOptionsContainer').find('.mobileSearch input').val(currentWord);
                        }

                        if(highlightItemPosition !== false){

                            let textElement = $('.customSelectBox.active .selectOptions div').eq(highlightItemPosition);

                            $('.customSelectBox.active .selectOptions').animate({
                                scrollTop: $(textElement).parent().scrollTop() + ($(textElement).offset().top - $(textElement).parent().offset().top)
                            }, 100);

                            $('.customSelectBox.active .selectOptions div').removeClass('predictiveHover');
                            $(textElement).addClass('predictiveHover');
                        }
                    }

                    return false;
                }
            });

        }else{
			console.log('Select box listeners already started!');
		}

        blListnersStarted = true;
	}
}