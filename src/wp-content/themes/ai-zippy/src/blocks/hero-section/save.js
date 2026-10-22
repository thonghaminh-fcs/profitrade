/**
 * Save function returns null because we use server-side rendering (render.php).
 * This makes the block dynamic — output is generated fresh on each page load.
 * Benefits: Can use PHP functions, always up-to-date, no block validation errors.
 */
export default function save() {
	return null;
}
