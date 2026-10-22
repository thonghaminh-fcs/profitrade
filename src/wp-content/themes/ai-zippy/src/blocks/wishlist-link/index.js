import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";
import "./editor.scss";

function Edit() {
	return (
		<div {...useBlockProps({ className: "az-wishlist-link-editor" })}>
			<span className="az-wishlist-link-editor__button" aria-hidden="true">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
					<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
				</svg>
			</span>
			<span className="screen-reader-text">{__("Wishlist link", "ai-zippy")}</span>
		</div>
	);
}

registerBlockType(metadata.name, {
	edit: Edit,
	save: () => null,
});
