import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

/**
 * Editor preview.
 *
 * The drawer is fixed-position and would cover the canvas, so
 * sections/_mini-cart.scss hides it (and the overlay) inside the editor;
 * only the red pill button is previewed, with the session's real cart data.
 */
export default function Edit({ attributes, setAttributes }) {
	const { expressFee } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Mini Cart", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("1-Hour Express fee", "ai-zippy-child")}
						help={__(
							"Added to the drawer total and sent to checkout when Express is selected.",
							"ai-zippy-child"
						)}
						type="number"
						min="0"
						step="0.01"
						value={String(expressFee)}
						onChange={(value) =>
							setAttributes({
								expressFee: Math.max(0, parseFloat(value) || 0),
							})
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender block="ai-zippy/sm-mini-cart" attributes={attributes} />
			</div>
		</>
	);
}
