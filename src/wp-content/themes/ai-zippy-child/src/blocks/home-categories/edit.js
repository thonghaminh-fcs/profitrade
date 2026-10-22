import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { heading, viewAllLabel, viewAllUrl } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Shop by Category", "ai-zippy-child")} initialOpen>
					<TextControl
						label={__("Heading", "ai-zippy-child")}
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
					/>
					<TextControl
						label={__("“View all” label", "ai-zippy-child")}
						value={viewAllLabel}
						onChange={(value) => setAttributes({ viewAllLabel: value })}
					/>
					<TextControl
						label={__("“View all” URL", "ai-zippy-child")}
						help={__("Defaults to the shop page (/shop/).", "ai-zippy-child")}
						value={viewAllUrl}
						onChange={(value) => setAttributes({ viewAllUrl: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/home-categories"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
