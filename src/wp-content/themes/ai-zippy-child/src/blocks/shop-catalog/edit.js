import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, RangeControl } from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";

export default function Edit({ attributes, setAttributes }) {
	const { perPage, columns } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Catalog", "ai-zippy-child")} initialOpen>
					<RangeControl
						label={__("Products per page", "ai-zippy-child")}
						help={__(
							"Number of products rendered in the catalog (14 = the full seeded catalog).",
							"ai-zippy-child"
						)}
						value={perPage}
						min={1}
						max={48}
						onChange={(value) =>
							setAttributes({ perPage: Number(value) || 14 })
						}
					/>
					<RangeControl
						label={__("Columns", "ai-zippy-child")}
						help={__(
							"0 = intrinsic grid (auto-fill, minmax 190px) like the mockup; any other value forces that many columns.",
							"ai-zippy-child"
						)}
						value={columns}
						min={0}
						max={6}
						onChange={(value) =>
							setAttributes({ columns: Number(value) || 0 })
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<ServerSideRender
					block="ai-zippy/shop-catalog"
					attributes={attributes}
				/>
			</div>
		</>
	);
}
