
const CustomCheckboxTPL = '<label class="switch checkbox-{{name}} {{required}}">'+
	'<input type="checkbox" name="{{name}}" {{checked}} {{disabled}} {{required}} value="{{value}}">'+
	'<div class="slider round"></div>'+
	'</label>';

import {Placeholders} from './Placeholders';

export class CustomCheckbox{

	constructor(){

		let Placeholder = new Placeholders();

		$('input[type=checkbox]').each(function(){

			let html = Placeholder.build(CustomCheckboxTPL,{
				name: $(this).attr('name'),
				required: ($(this).is('[required]')) ? 'required' : '',
				checked: ($(this).is('[checked]')) ? 'checked' : '',
				disabled: ($(this).is('[disabled]')) ? 'disabled' : '',
				value: $(this).val()
			});

			$(this).replaceWith($( html ));
		});

		this.startListeners();
	}

	startListeners(){

	}
}