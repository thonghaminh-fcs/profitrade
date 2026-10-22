<?php

/**
 * Server-side render for Hero Section block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

$tagline          = esc_html($attributes['tagline'] ?? '');
$heading          = nl2br(esc_html($attributes['heading'] ?? ''));
$primary_btn_text = esc_html($attributes['primaryBtnText'] ?? '');
$primary_btn_url  = esc_url($attributes['primaryBtnUrl'] ?? '#');
$phone_label      = esc_html($attributes['phoneLabel'] ?? '');
$phone_number     = esc_html($attributes['phoneNumber'] ?? '');
$media_url        = esc_url($attributes['mediaUrl'] ?? '');
$media_type       = esc_attr($attributes['mediaType'] ?? 'image');
$video_url        = esc_url($attributes['videoUrl'] ?? '');
$person_image_url = esc_url($attributes['personImageUrl'] ?? '');

$is_video = $media_type === 'video' || !empty($video_url);

$wrapper_attributes = get_block_wrapper_attributes();
?>

<div <?php echo $wrapper_attributes; ?>>
    <div class="hero-section__bg"></div>

    <div class="hero-section__container">
        <!-- Left column -->
        <div class="hero-section__content">
            <?php if ($tagline) : ?>
                <span class="hero-section__tagline"><?php echo $tagline; ?></span>
            <?php endif; ?>

            <?php if ($heading) : ?>
                <h1 class="hero-section__heading"><?php echo $heading; ?></h1>
            <?php endif; ?>

            <?php if ($primary_btn_text) : ?>
                <div class="hero-section__cta">
                    <a href="<?php echo $primary_btn_url; ?>" class="hero-section__btn">
                        <span><?php echo $primary_btn_text; ?></span>
                        <svg class="hero-section__btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="7" y1="17" x2="17" y2="7"></line>
                            <polyline points="7 7 17 7 17 17"></polyline>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($phone_number) : ?>
                <div class="hero-section__phone">
                    <span class="hero-section__phone-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </span>
                    <div class="hero-section__phone-text">
                        <?php if ($phone_label) : ?>
                            <span class="hero-section__phone-label"><?php echo $phone_label; ?></span>
                        <?php endif; ?>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $phone_number); ?>" class="hero-section__phone-number">
                            <?php echo $phone_number; ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right column - Media card -->
        <div class="hero-section__media">
            <div class="hero-section__card">
                <?php if ($media_url) : ?>
                    <img src="<?php echo $media_url; ?>" alt="" class="hero-section__card-img" loading="lazy" />
                <?php endif; ?>

                <?php if ($is_video) : ?>
                    <?php if ($video_url) : ?>
                        <a href="<?php echo $video_url; ?>" class="hero-section__play-btn" target="_blank" rel="noopener noreferrer" aria-label="Play video">
                    <?php else : ?>
                        <span class="hero-section__play-btn">
                    <?php endif; ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                    <?php if ($video_url) : ?>
                        </a>
                    <?php else : ?>
                        </span>
                    <?php endif; ?>

                    <div class="hero-section__progress">
                        <div class="hero-section__progress-track">
                            <div class="hero-section__progress-thumb"></div>
                        </div>
                        <span class="hero-section__progress-sound">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
                            </svg>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Person foreground image -->
        <?php if ($person_image_url) : ?>
            <div class="hero-section__person">
                <img src="<?php echo $person_image_url; ?>" alt="" class="hero-section__person-img" loading="lazy" />
            </div>
        <?php endif; ?>
    </div>
</div>
