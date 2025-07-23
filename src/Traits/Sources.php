<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2025
 * @license   MIT License
 */

namespace JuniWalk\DataTable\Traits;

use JuniWalk\DataTable\Source;

trait Sources
{
	private string $primaryKey = 'id';
	private Source $source;


	public function setPrimaryKey(string $primaryKey): self
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}


	// todo: allow dynamic source creation from given data type (in different method)
	public function setSource(Source $source): self
	{
		$this->source = $source;
		return $this;
	}
}
