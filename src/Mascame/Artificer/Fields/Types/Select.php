<?php namespace Mascame\Artificer\Fields\Types;

use Mascame\Artificer\Fields\AbstractField;
use Form;

class Select extends AbstractField {

	public function input()
	{
		return Form::select($this->name, $this->value, false, $this->attributes->all());
	}

	public function outputRange($start, $end)
	{
		return Form::selectRange($this->name, $start, $end);
	}

	public function outputMonth()
	{
		return Form::selectMonth($this->name);
	}

	public function outputYear()
	{
		return Form::selectYear($this->name);
	}
}