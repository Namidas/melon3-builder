<?php

M3::reqVendor('commonmark');
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Node\Node;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Block\Document;

final class AvoidParagraphTagsIfOnlyParagraphRenderer implements NodeRendererInterface
{
    private ParagraphRenderer $normalRenderer;

    public function __construct(ParagraphRenderer $normalRenderer)
    {
		$this->normalRenderer = $normalRenderer;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
		// Is this paragraph the only block in the document?
		//__vdump($node->parent() instanceof Document && $node->previous() === null && $node->next() === null,$node);/*die();*/
        if ($node->parent() instanceof Document && $node->previous() === null && $node->next() === null) {
            // Yes - so let's render it's children without the usual wrapping <p> tags
			//die("-- ABNORMAL RENDER");
            return $childRenderer->renderNodes($node->children()) . $childRenderer->getBlockSeparator();
        }

        // Otherwise, render as usual
        return $this->normalRenderer->render($node, $childRenderer);
    }
}

function fn_pkg_get_formatter_md_instance($opts=Array())
{
	$instance = new CommonMarkConverter(array_merge([
		'html_input' => 'allow',
		//'allow_unsafe_links' => false,
	],$opts));
	
	$environment = $instance->getEnvironment();
	$environment->addRenderer(Paragraph::class, new AvoidParagraphTagsIfOnlyParagraphRenderer(new ParagraphRenderer()));

	return $instance;
}

function fn_pkg_get_formatted_md($content,$opts)
{
	$converter = fn_pkg_get_formatter_md_instance($opts);
	return $converter->convert($content)->getContent();
}
			
?>