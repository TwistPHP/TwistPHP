import {CustomCheckbox} from "./CustomCheckbox";
import {CustomSelect} from "./CustomSelect";

export class AjaxRequest{

	constructor(strTwistAjaxURL){

		this.API = new twistajax( strTwistAjaxURL );
		this.ajaxLoader = null;
		this.ajaxTimeout = null;

		this.startupModal();

		return this;
	}

	startupModal(){

		//Register the close modal function
		$(document).on('click','#modalWindow a.close',function(e){
			e.preventDefault();
			$('body').removeClass('modal-is-open');
			$('#modalWindow').removeClass('open').find('.modalContent').html('');
		});

		//Open a modal based on URL parameters
		let modalLink = AjaxRequest.parseSecond('modal');
		let modalLinkRel = AjaxRequest.parseSecond('rel');

		if(modalLink !== '' && modalLinkRel !== ''){
			this.htmlModal('<h2>Loading ...</h2>');
			this.openModal(modalLink,{id: modalLinkRel},null);
		}
	}

	static parseSecond(val){

		let result = "",
			tmp = [];
		let items = location.search.substr(1).split("&");

		for (let index = 0; index < items.length; index++) {
			tmp = items[index].split("=");
			if (tmp[0] === val) result = decodeURIComponent(tmp[1]);
		}

		return result;
	}

	fixIE11(){

		//IE11 fix, bug with flex and overflow auto stoping the modal from scrolling
		//Dammit IE11 you massive ballake!!
		$('#modalWindow').find('a.close').hide();
		$('#modalWindow').find('.modalContent').hide();
		setTimeout(function(){
			$('#modalWindow').find('.modalContent').fadeIn();
		},100);
		setTimeout(function(){
			$('#modalWindow').find('a.close').fadeIn();
		},120);
	}

	htmlModal(strHTMLCode){

		//Output the response into the modal box
		$('#modalWindow').addClass('open').find('.modalContent').html(strHTMLCode);

		$('body').addClass('modal-is-open');
		this.fixIE11();
	}

	openModal(strAjaxFunction, jsonPostData, strLoaderElement, intTimeout = 1200, intLoaderAppear = 200){

		this.loaderClear(strLoaderElement);

		clearTimeout(this.ajaxLoader);
		clearTimeout(this.ajaxTimeout);

		let thisLoaderElement = strLoaderElement;
		let thisModalBox = this;

		if(intTimeout < 200){
			intTimeout = 201;
		}

		//Start the AJAX request 200 milliseconds before the timeout
		this.ajaxTimeout = setTimeout(function(){

			//Make the AJAX request
			thisModalBox.API.post( strAjaxFunction, jsonPostData)
				.then( response => {

					if(response.redirect){
						document.location.href = response.redirect;
					}else{

						//Output the response into the modal box
						$('#modalWindow').addClass('open').find('.modalContent').html(response.html);
						$('body').addClass('modal-is-open');
						thisModalBox.fixIE11();

						//Customised the select boxes
						new CustomSelect();

						//Customised the checkboxes boxes
						new CustomCheckbox();

						//Clear the ajax loader
						thisModalBox.loaderClear(thisLoaderElement,true);
					}
				} )
				.catch( e => {
					console.error( 'ajax error:', e );
					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement);
				} );

		},intTimeout-200);

		if(intLoaderAppear > 0){
			//Start the loader after 200 milliseconds
			this.ajaxLoader = setTimeout(function(){
				thisModalBox.loader(thisLoaderElement);
			},intLoaderAppear);
		}

		return this;
	}

	openModalForm(strAjaxFunction, strFormID, strLoaderElement, intTimeout = 1200, intLoaderAppear = 200){

		this.loaderClear(strLoaderElement);

		clearTimeout(this.ajaxLoader);
		clearTimeout(this.ajaxTimeout);

		let thisLoaderElement = strLoaderElement;
		let thisModalBox = this;

		if(intTimeout < 200){
			intTimeout = 201;
		}

		//Start the AJAX request 200 milliseconds before the timeout
		this.ajaxTimeout = setTimeout(function(){

			//Make the AJAX request
			thisModalBox.API.postForm( strAjaxFunction, strFormID)
				.then( response => {

					//Output the response into the modal box
					$('#modalWindow').addClass('open').find('.modalContent').html(response.html);
					$('body').addClass('modal-is-open');
					thisModalBox.fixIE11();

					//Customised the select boxes
					new CustomSelect();

					//Customised the checkboxes boxes
					new CustomCheckbox();

					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement,true);
				} )
				.catch( e => {
					console.error( 'ajax error:', e );
					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement);
				} );

		},intTimeout-200);

		if(intLoaderAppear > 0){
			//Start the loader after 200 milliseconds
			this.ajaxLoader = setTimeout(function(){
				thisModalBox.loader(thisLoaderElement);
			},intLoaderAppear);
		}

		return this;
	}

	standard(strAjaxFunction, strFormID, strLoaderElement, strResponseElement, intTimeout = 1900, intLoaderAppear = 400){

		this.loaderClear(strLoaderElement);

		clearTimeout(this.ajaxLoader);
		clearTimeout(this.ajaxTimeout);

		let thisLoaderElement = strLoaderElement;
		let thisResponseElement = strResponseElement;
		let thisModalBox = this;

		if(intTimeout < 200){
			intTimeout = 201;
		}

		//Start the AJAX request 200 milliseconds before the timeout
		this.ajaxTimeout = setTimeout(function(){

			//Make the AJAX request
			thisModalBox.API.postForm( strAjaxFunction, strFormID)
				.then( response => {

					if(thisResponseElement !== null){

						//Output the response into the modal box
						$(thisResponseElement).html(response.html);

						//Customised the select boxes
						new CustomSelect();

						//Customised the checkboxes boxes
						new CustomCheckbox();
					}

					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement,true);
				} )
				.catch( e => {
					console.error( 'ajax error:', e );
					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement);
				} );

		},intTimeout-200);

		if(intLoaderAppear > 0){
			//Start the loader after 200 milliseconds
			this.ajaxLoader = setTimeout(function(){
				thisModalBox.loader(thisLoaderElement);
			},intLoaderAppear);
		}

		return this;
	}

	request(strAjaxFunction, jsonPostData, strLoaderElement, strResponseElement, intTimeout = 1900, intLoaderAppear = 400){

		this.loaderClear(strLoaderElement);

		clearTimeout(this.ajaxLoader);
		clearTimeout(this.ajaxTimeout);

		let thisLoaderElement = strLoaderElement;
		let thisResponseElement = strResponseElement;
		let thisModalBox = this;

		if(intTimeout < 200){
			intTimeout = 201;
		}

		//Start the AJAX request 200 milliseconds before the timeout
		this.ajaxTimeout = setTimeout(function(){

			//Make the AJAX request
			thisModalBox.API.post( strAjaxFunction, jsonPostData)
				.then( response => {

					if(thisResponseElement !== null){

						//Output the response into the modal box
						$(thisResponseElement).html(response.html);

						//Customised the select boxes
						new CustomSelect();

						//Customised the checkboxes boxes
						new CustomCheckbox();
					}

					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement,true);
				} )
				.catch( e => {
					console.error( 'ajax error:', e );
					//Clear the ajax loader
					thisModalBox.loaderClear(thisLoaderElement);
				} );

		},intTimeout-200);

		if(intLoaderAppear > 0){
			//Start the loader after 200 milliseconds
			this.ajaxLoader = setTimeout(function(){
				thisModalBox.loader(thisLoaderElement);
			},intLoaderAppear);
		}

		return this;
	}

	loader(strLoaderElement){

		if($(strLoaderElement).length && this.ajaxTimeout){

			console.log('Loader');

			let strNodeName = $(strLoaderElement).get(0).nodeName.toLowerCase();

			if(strNodeName === "div"){

				$(strLoaderElement).addClass('loading');

			}else if(strNodeName === "form"){

				$(strLoaderElement).find('button[type="submit"]').addClass('loader').addClass('loading').attr('disabled', true);

			}else if(strNodeName === "a" && $(strLoaderElement).hasClass('longButton')){

				$(strLoaderElement).addClass('loading');

			}else if(strNodeName === "a" && $(strLoaderElement).hasClass('button')){

				$(strLoaderElement).addClass('loader').addClass('loading');

			}else if(strNodeName === "a"){

				$(strLoaderElement).addClass('linkLoader').addClass('loading');
			}
		}
	}

	loaderClear(strLoaderElement,blProcessCallback = false){

		if($(strLoaderElement).length && this.ajaxTimeout !== null){

			console.log('Loader Clear');

			let strNodeName = $(strLoaderElement).get(0).nodeName.toLowerCase();

			if(strNodeName === "div"){

				$(strLoaderElement).removeClass('loading');

			}else if(strNodeName === "form"){

				$(strLoaderElement).find('button[type="submit"]').removeClass('loading').attr('disabled', false);

			}else if(strNodeName === "a" && $(strLoaderElement).hasClass('longButton')){

				$(strLoaderElement).removeClass('loading');

			}else if(strNodeName === "a" && $(strLoaderElement).hasClass('button')){

				$(strLoaderElement).removeClass('loading');

			}else if(strNodeName === "a"){

				$(strLoaderElement).removeClass('loading');
			}
		}

		let CallBackFunction = $(strLoaderElement).attr('data-callback');
		if(blProcessCallback && CallBackFunction !== '' && typeof this[CallBackFunction] === 'function'){
			this[CallBackFunction](strLoaderElement);
		}

		//Resize to ensure the correct placement of the footer
		$( window ).resize();
	}

	clearNotification(loaderElement){
		$(loaderElement).parent().remove();
		$('.notifications .notificationContainer').html($('#modalWindow .modalContent').html());

		let notificationValue = ($('.notifications span.notices').html() * 1);
		notificationValue = notificationValue - 1;

		if(notificationValue > 0){
			$('.notifications .notices').html(notificationValue);
		}else{
			$('.notifications .notices').html(notificationValue).hide();
		}
	}

	clearAllNotifications(){
		$('.currentNotifications ul').html('');
	}
}