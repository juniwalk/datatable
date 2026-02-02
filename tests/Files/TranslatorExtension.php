<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\FilterNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Nodes\PrintNode;
use Latte\Compiler\Tag;
use Latte\Extension;
use Latte\Runtime\FilterInfo;
use Nette\Localization\Translator;
use Stringable;

/**
 * Code taken and heavily modified from TranslatorExtension of the Contributte/Translation package
 * @link https://github.com/contributte/translation/blob/master/src/Latte/TranslatorExtension.php
 */
class TranslatorExtension extends Extension implements Translator
{
	public function translate(Stringable|string|null $message, mixed ...$params): string
	{
		return (string) $message;
	}


	public function getFilters(): array
	{
		return [
			'translate' => fn(FilterInfo $fi, ...$args) => $this->translate(...$args),
		];
	}


	public function getTags(): array
	{
		return [
			'_' => $this->parseTranslate(...),
		];
	}


	protected function parseTranslate(Tag $tag): PrintNode
	{
		$tag->expectArguments();
		$args = new ArrayNode;

		$output = new PrintNode;
		$output->expression = $tag->parser->parseUnquotedStringOrExpression();

		if ($tag->parser->stream->tryConsume(',') !== null) {
			$args = $tag->parser->parseArguments();
		}

		$output->modifier = $tag->parser->parseModifier();
		$output->modifier->escape = $output->modifier->removeFilter('noescape') === null;
		$output->modifier->filters[] = new FilterNode(
			new IdentifierNode('translate'),
			$args->toArguments(),
		);

		return $output;
	}
}
