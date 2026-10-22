import { registerBlockType } from "@wordpress/blocks";
import Edit from "./edit.js";
import save from "./save.js";
import metadata from "./block.json";
import "./style.scss";
import "./editor.scss";

registerBlockType(metadata.name, {
	edit: Edit,
	save,
});
