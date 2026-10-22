import { __ } from "@wordpress/i18n";
import {
	useBlockProps,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from "@wordpress/block-editor";
import {
	PanelBody,
	Button,
	SelectControl,
	TextControl,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
	const {
		tagline,
		heading,
		primaryBtnText,
		primaryBtnUrl,
		phoneLabel,
		phoneNumber,
		mediaUrl,
		mediaId,
		mediaType,
		videoUrl,
		personImageUrl,
		personImageId,
	} = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__("Button Settings", "ai-zippy")}
					initialOpen={true}
				>
					<TextControl
						label={__("Button URL", "ai-zippy")}
						value={primaryBtnUrl}
						onChange={(val) =>
							setAttributes({ primaryBtnUrl: val })
						}
					/>
					<TextControl
						label={__("Phone Label", "ai-zippy")}
						value={phoneLabel}
						onChange={(val) =>
							setAttributes({ phoneLabel: val })
						}
					/>
					<TextControl
						label={__("Phone Number", "ai-zippy")}
						value={phoneNumber}
						onChange={(val) =>
							setAttributes({ phoneNumber: val })
						}
					/>
				</PanelBody>

				<PanelBody
					title={__("Media Card", "ai-zippy")}
					initialOpen={true}
				>
					<SelectControl
						label={__("Media Type", "ai-zippy")}
						value={mediaType}
						options={[
							{ label: "Image", value: "image" },
							{ label: "Video", value: "video" },
						]}
						onChange={(val) =>
							setAttributes({ mediaType: val })
						}
					/>
					{mediaType === "video" && (
						<TextControl
							label={__("Video URL", "ai-zippy")}
							value={videoUrl}
							onChange={(val) =>
								setAttributes({ videoUrl: val })
							}
							help={__(
								"YouTube, Vimeo, or direct video URL",
								"ai-zippy",
							)}
						/>
					)}
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) =>
								setAttributes({
									mediaId: media.id,
									mediaUrl: media.url,
								})
							}
							allowedTypes={["image", "video"]}
							value={mediaId}
							render={({ open }) => (
								<div style={{ marginTop: "8px" }}>
									<p style={{ marginBottom: "4px" }}>
										<strong>
											{mediaType === "video"
												? __(
														"Thumbnail / Cover",
														"ai-zippy",
													)
												: __("Card Image", "ai-zippy")}
										</strong>
									</p>
									{mediaUrl && (
										<img
											src={mediaUrl}
											alt=""
											style={{
												maxWidth: "100%",
												marginBottom: "8px",
												borderRadius: "8px",
											}}
										/>
									)}
									<Button
										onClick={open}
										variant="secondary"
									>
										{mediaUrl
											? __("Replace", "ai-zippy")
											: __("Select Image", "ai-zippy")}
									</Button>
									{mediaUrl && (
										<Button
											onClick={() =>
												setAttributes({
													mediaId: 0,
													mediaUrl: "",
												})
											}
											variant="link"
											isDestructive
											style={{ marginLeft: "8px" }}
										>
											{__("Remove", "ai-zippy")}
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>

				<PanelBody
					title={__("Person Image (Foreground)", "ai-zippy")}
					initialOpen={false}
				>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) =>
								setAttributes({
									personImageId: media.id,
									personImageUrl: media.url,
								})
							}
							allowedTypes={["image"]}
							value={personImageId}
							render={({ open }) => (
								<div>
									{personImageUrl && (
										<img
											src={personImageUrl}
											alt=""
											style={{
												maxWidth: "100%",
												marginBottom: "8px",
												borderRadius: "8px",
											}}
										/>
									)}
									<Button
										onClick={open}
										variant="secondary"
									>
										{personImageUrl
											? __("Replace", "ai-zippy")
											: __("Select Image", "ai-zippy")}
									</Button>
									{personImageUrl && (
										<Button
											onClick={() =>
												setAttributes({
													personImageId: 0,
													personImageUrl: "",
												})
											}
											variant="link"
											isDestructive
											style={{ marginLeft: "8px" }}
										>
											{__("Remove", "ai-zippy")}
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{/* Background gradient */}
				<div className="hero-section__bg" />

				<div className="hero-section__container">
					{/* Left column - Content */}
					<div className="hero-section__content">
						<RichText
							tagName="span"
							className="hero-section__tagline"
							value={tagline}
							onChange={(val) =>
								setAttributes({ tagline: val })
							}
							placeholder={__("Build yourself", "ai-zippy")}
						/>

						<RichText
							tagName="h1"
							className="hero-section__heading"
							value={heading}
							onChange={(val) =>
								setAttributes({ heading: val })
							}
							placeholder={__("Be master\nwith us", "ai-zippy")}
						/>

						<div className="hero-section__cta">
							<RichText
								tagName="span"
								className="hero-section__btn"
								value={primaryBtnText}
								onChange={(val) =>
									setAttributes({ primaryBtnText: val })
								}
								placeholder={__(
									"Enroll Today",
									"ai-zippy",
								)}
							/>
						</div>

						<div className="hero-section__phone">
							<span className="hero-section__phone-icon">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
									<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
								</svg>
							</span>
							<div className="hero-section__phone-text">
								<span className="hero-section__phone-label">
									{phoneLabel}
								</span>
								<span className="hero-section__phone-number">
									{phoneNumber}
								</span>
							</div>
						</div>
					</div>

					{/* Right column - Media card */}
					<div className="hero-section__media">
						<div className="hero-section__card">
							{mediaUrl ? (
								<img
									src={mediaUrl}
									alt=""
									className="hero-section__card-img"
								/>
							) : (
								<div className="hero-section__card-placeholder">
									<span>
										{__("Select media in sidebar", "ai-zippy")}
									</span>
								</div>
							)}
							{(mediaType === "video" || videoUrl) && (
								<div className="hero-section__play-btn">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
										<polygon points="5 3 19 12 5 21 5 3" />
									</svg>
								</div>
							)}
						</div>
					</div>

					{/* Person foreground image */}
					{personImageUrl && (
						<div className="hero-section__person">
							<img
								src={personImageUrl}
								alt=""
								className="hero-section__person-img"
							/>
						</div>
					)}
				</div>
			</div>
		</>
	);
}
