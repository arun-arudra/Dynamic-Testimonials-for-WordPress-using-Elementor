<?php

if (! defined('WP_DEBUG')) {
	die( 'Direct access forbidden.' );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});



/**
 * Register a custom post type for Testimonials.
 */
if ( ! function_exists( 'create_testimonial_post_type' ) ) {
    function create_testimonial_post_type() {
        $labels = array(
            'name'                => _x( 'Testimonials', 'Post type general name', 'your-text-domain' ),
            'singular_name'       => _x( 'Testimonial', 'Post type singular name', 'your-text-domain' ),
            'menu_name'           => _x( 'Testimonials', 'Admin Menu text', 'your-text-domain' ),
            'name_admin_bar'      => _x( 'Testimonial', 'Add New on Toolbar', 'your-text-domain' ),
            'add_new'             => __( 'Add New', 'your-text-domain' ),
            'add_new_item'        => __( 'Add New Testimonial', 'your-text-domain' ),
            'new_item'            => __( 'New Testimonial', 'your-text-domain' ),
            'edit_item'           => __( 'Edit Testimonial', 'your-text-domain' ),
            'view_item'           => __( 'View Testimonial', 'your-text-domain' ),
            'all_items'           => __( 'All Testimonials', 'your-text-domain' ),
            'search_items'        => __( 'Search Testimonials', 'your-text-domain' ),
            'parent_item_colon'   => __( 'Parent Testimonials:', 'your-text-domain' ),
            'not_found'           => __( 'No testimonials found.', 'your-text-domain' ),
            'not_found_in_trash'  => __( 'No testimonials found in Trash.', 'your-text-domain' ),
            'featured_image'      => _x( 'Client Photo', 'Overrides the “Featured Image” phrase for this post type. Added since 4.3', 'your-text-domain' ),
            'set_featured_image'  => _x( 'Set client photo', 'Overrides the “Set featured image” phrase for this post type. Added since 4.3', 'your-text-domain' ),
            'remove_featured_image' => _x( 'Remove client photo', 'Overrides the “Remove featured image” phrase for this post type. Added since 4.3', 'your-text-domain' ),
            'use_featured_image'  => _x( 'Use as client photo', 'Overrides the “Use as client photo” phrase for this post type. Added since 4.3', 'your-text-domain' ),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'testimonial' ),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'supports'            => array( 'title', 'editor', 'author', 'thumbnail' ),
            'menu_icon'           => 'dashicons-format-quote',
        );

        register_post_type( 'testimonial', $args );
    }
    add_action( 'init', 'create_testimonial_post_type' );
}

/**
 * Add custom meta boxes for testimonial details.
 */
if ( ! function_exists( 'add_testimonial_metaboxes' ) ) {
    function add_testimonial_metaboxes() {
        add_meta_box(
            'testimonial_details',
            'Testimonial Details',
            'render_testimonial_details_metabox',
            'testimonial',
            'normal',
            'high'
        );
    }
    add_action( 'add_meta_boxes', 'add_testimonial_metaboxes' );
}

/**
 * Render the testimonial details meta box.
 */
if ( ! function_exists( 'render_testimonial_details_metabox' ) ) {
    function render_testimonial_details_metabox( $post ) {
        wp_nonce_field( basename( __FILE__ ), 'testimonial_nonce' );
        $product_name = get_post_meta( $post->ID, 'product_name', true );
        $video_url = get_post_meta( $post->ID, 'video_url', true );
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="product_name">Product Name</label></th>
                    <td><input type="text" name="product_name" id="product_name" value="<?php echo esc_attr( $product_name ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="video_url">Video URL (e.g., YouTube)</label></th>
                    <td><input type="url" name="video_url" id="video_url" value="<?php echo esc_attr( $video_url ); ?>" class="regular-text" /></td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}

/**
 * Save the testimonial details.
 */
if ( ! function_exists( 'save_testimonial_details' ) ) {
    function save_testimonial_details( $post_id ) {
        if ( ! isset( $_POST['testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_nonce'], basename( __FILE__ ) ) ) {
            return $post_id;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        if ( 'testimonial' == $_POST['post_type'] && ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        if ( isset( $_POST['product_name'] ) ) {
            update_post_meta( $post_id, 'product_name', sanitize_text_field( $_POST['product_name'] ) );
        }

        if ( isset( $_POST['video_url'] ) ) {
            update_post_meta( $post_id, 'video_url', esc_url_raw( $_POST['video_url'] ) );
        }
    }
    add_action( 'save_post', 'save_testimonial_details' );
}

/**
 * Create a shortcode to display testimonials using an Elementor template.
 */
if ( ! function_exists( 'display_testimonials_shortcode_from_template' ) ) {
    function display_testimonials_shortcode_from_template( $atts ) {
        $atts = shortcode_atts( array(
            'template_id' => '',
        ), $atts, 'testimonials' );

        $template_id = (int) $atts['template_id'];

        if ( empty( $template_id ) ) {
            return '<p>Please provide an Elementor template ID for the testimonials shortcode.</p>';
        }

        $testimonials_query = new WP_Query( array(
            'post_type'      => 'testimonial',
            'posts_per_page' => -1,
            'order'          => 'DESC',
            'orderby'        => 'date',
        ) );

        if ( $testimonials_query->have_posts() ) {
            $testimonials_data = [];
            while ( $testimonials_query->have_posts() ) {
                $testimonials_query->the_post();
                $testimonials_data[] = [
                    'name'    => get_the_title(),
                    'content' => apply_filters( 'the_content', get_the_content() ),
                    'image'   => get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: '',
                    'video'   => get_post_meta( get_the_ID(), 'video_url', true ) ?: '',
                    'product' => get_post_meta( get_the_ID(), 'product_name', true ) ?: '',
                ];
            }
            wp_reset_postdata();

            // Elementor's plugin instance check is important
            if ( ! did_action( 'elementor/loaded' ) || ! class_exists( 'Elementor\Plugin' ) ) {
                return '<p>Elementor is not active or not fully loaded.</p>';
            }
            $template_html = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
            
            ob_start();
            ?>
            <div class="testimonial-shortcode-container" data-testimonials='<?php echo htmlspecialchars(json_encode($testimonials_data), ENT_QUOTES, 'UTF-8'); ?>'>
                <div class="testimonial-item-template" style="display: none;">
                    <?php echo $template_html; ?>
                </div>
                
                <div class="testimonial-dynamic-content"></div>

                <div class="carousel-indicators"></div>

                <div class="video-modal">
                    <div class="video-modal-content">
                        <span class="close-button">×</span>
                        <div class="video-container"></div>
                    </div>
                </div>
            </div>
            <?php
            $output = ob_get_clean();
            return $output;
        }
        return '<p>No testimonials found.</p>';
    }
    add_shortcode( 'testimonials', 'display_testimonials_shortcode_from_template' );
}

/**
 * Enqueue scripts and styles.
 */
if ( ! function_exists( 'enqueue_testimonial_assets' ) ) {
    function enqueue_testimonial_assets() {
        wp_enqueue_script( 'testimonial-script', get_stylesheet_directory_uri() . '/assets/js/testimonial-carousel.js', array( 'jquery' ), null, true );
        wp_enqueue_style( 'testimonial-style', get_stylesheet_directory_uri() . '/assets/css/testimonial-style.css', array(), null );
    }
    add_action( 'wp_enqueue_scripts', 'enqueue_testimonial_assets' );
}

/**
 * Add CSS and JS files to the theme.
 * To use this, you must create an 'assets' folder in your theme,
 * and inside it, create 'css' and 'js' folders.
 */
if ( ! function_exists( 'create_testimonial_assets_files' ) ) {
    function create_testimonial_assets_files() {
        // Define file paths
        $js_dir = get_stylesheet_directory() . '/assets/js/';
        $css_dir = get_stylesheet_directory() . '/assets/css/';

        // Create directories if they don't exist
        if (!is_dir($js_dir)) { mkdir($js_dir, 0755, true); }
        if (!is_dir($css_dir)) { mkdir($css_dir, 0755, true); }

        // JS file content
        $js_content = '
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM fully loaded. Initializing carousel observer.");
    
    // Function to initialize the carousel for a given container
    function initializeCarousel(container) {
        if (container.classList.contains("carousel-initialized")) {
            console.log("Carousel already initialized for this container, skipping.");
            return;
        }
        container.classList.add("carousel-initialized");
        console.log("Initializing carousel for a new container.");

        const testimonialsData = JSON.parse(container.getAttribute("data-testimonials"));
        const templateItem = container.querySelector(".testimonial-item-template");
        const dynamicContent = container.querySelector(".testimonial-dynamic-content");
        
        const indicatorsContainer = container.querySelector(".carousel-indicators");
        console.log("Found indicators container:", !!indicatorsContainer);

        const videoModal = container.querySelector(".video-modal");
        const videoContainer = videoModal ? videoModal.querySelector(".video-container") : null;
        const closeButton = videoModal ? videoModal.querySelector(".close-button") : null;
        
        if (!testimonialsData || testimonialsData.length === 0) {
            console.warn("No testimonial data found. Hiding navigation.");
            if (indicatorsContainer) indicatorsContainer.style.display = "none";
            return;
        }

        let currentIndex = 0;
        let intervalId;

        // Wrap the update in a small delay to ensure Elementor\'s DOM is ready
        setTimeout(() => {
            updateCarousel(true);
            startAutoPlay();
        }, 50);

        function updateCarousel(isInitialLoad = false, direction = "next") {
            console.log("Updating carousel to slide:", currentIndex, "in direction:", direction);
            if (!templateItem || !dynamicContent) {
                console.error("Template or dynamic content container not found.");
                return;
            }

            const currentData = testimonialsData[currentIndex];
            const newElement = templateItem.cloneNode(true);
            
            // Build the new element and its content
            const contentWrapper = newElement.querySelector(".testimonial-content-wrapper");
            const nameElement = newElement.querySelector(".elementor-testimonial-name");
            const contentElement = newElement.querySelector(".elementor-testimonial-content");
            const imageContainer = newElement.querySelector(".testimonial-image-container");
            const productElement = newElement.querySelector(".elementor-testimonial-product");
            const playButton = newElement.querySelector(".play-button");

            if (nameElement) nameElement.textContent = currentData.name;
            if (contentElement) contentElement.innerHTML = currentData.content;
            if (imageContainer) {
                imageContainer.style.backgroundImage = `url(${currentData.image})`;
                imageContainer.style.backgroundSize = "cover";
                imageContainer.style.backgroundPosition = "center center";
            }
            if (productElement) productElement.textContent = currentData.product;
            if (playButton && currentData.video) {
                playButton.style.display = "block";
                playButton.onclick = () => showVideoModal(currentData.video);
            } else if (playButton) {
                playButton.style.display = "none";
            }

            // Corrected logic: append a temporary version of the new element to measure height
            const tempElement = newElement.cloneNode(true);
            tempElement.style.position = "absolute";
            tempElement.style.visibility = "hidden";
            tempElement.style.display = "block";
            document.body.appendChild(tempElement);
            const newHeight = tempElement.scrollHeight;
            document.body.removeChild(tempElement);
            dynamicContent.style.height = newHeight + "px";
            
            // Handle the old element (slide out and then remove)
            const oldElement = dynamicContent.querySelector(".testimonial-item-template.is-active");
            if (oldElement && !isInitialLoad) {
                const oldContentWrapper = oldElement.querySelector(".testimonial-content-wrapper");
                if (oldContentWrapper) {
                    oldContentWrapper.classList.remove("slide-in-from-right", "slide-in-from-left");
                    if (direction === "next") {
                        oldContentWrapper.classList.add("slide-out-to-left");
                    } else {
                        oldContentWrapper.classList.add("slide-out-to-right");
                    }
                    oldContentWrapper.addEventListener("animationend", () => {
                        oldElement.remove();
                    }, { once: true });
                }
            } else if (oldElement) {
                // Remove the old element on initial load if it exists
                oldElement.remove();
            }

            // Now, append the actual new element and make it visible and active
            newElement.style.visibility = "visible";
            newElement.style.display = "block";
            newElement.classList.add("is-active");
            
            if (contentWrapper) {
                if (direction === "next") {
                    contentWrapper.classList.add("slide-in-from-right");
                } else {
                    contentWrapper.classList.add("slide-in-from-left");
                }
            }
            dynamicContent.appendChild(newElement);

            updateIndicators();
        }

        function updateIndicators() {
            if (!indicatorsContainer) return;
            indicatorsContainer.innerHTML = "";
            for (let i = 0; i < testimonialsData.length; i++) {
                const indicator = document.createElement("span");
                indicator.classList.add("indicator");
                if (i === currentIndex) {
                    indicator.classList.add("active");
                }
                indicator.addEventListener("click", () => {
                    console.log("Indicator clicked:", i);
                    const direction = i > currentIndex ? "next" : "prev";
                    currentIndex = i;
                    updateCarousel(false, direction);
                    resetAutoPlay();
                });
                indicatorsContainer.appendChild(indicator);
            }
        }

        function nextSlide() {
            console.log("Moving to next slide.");
            currentIndex = (currentIndex + 1) % testimonialsData.length;
            updateCarousel(false, "next");
        }

        function prevSlide() {
            console.log("Moving to previous slide.");
            currentIndex = (currentIndex - 1 + testimonialsData.length) % testimonialsData.length;
            updateCarousel(false, "prev");
        }

        function startAutoPlay() {
            intervalId = setInterval(nextSlide, 5000);
        }

        function resetAutoPlay() {
            clearInterval(intervalId);
            startAutoPlay();
        }

        function showVideoModal(videoUrl) {
            console.log("Showing video modal for URL:", videoUrl);
            videoModal.style.display = "block";
            const videoId = videoUrl.split("v=")[1];
            const iframeSrc = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            if (videoContainer) videoContainer.innerHTML = `<iframe src="${iframeSrc}" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
        }

        function hideVideoModal() {
            console.log("Hiding video modal.");
            if (videoModal) videoModal.style.display = "none";
            if (videoContainer) videoContainer.innerHTML = "";
        }
        
        document.body.addEventListener("click", (event) => {
            const prevButton = event.target.closest(".prev-button");
            const nextButton = event.target.closest(".next-button");

            if (prevButton) {
                console.log("Previous button clicked. Triggering prevSlide.");
                prevSlide();
                resetAutoPlay();
            } else if (nextButton) {
                console.log("Next button clicked. Triggering nextSlide.");
                nextSlide();
                resetAutoPlay();
            }
        });
        
        if (closeButton) closeButton.addEventListener("click", hideVideoModal);

        window.addEventListener("click", (event) => {
            if (event.target === videoModal) {
                hideVideoModal();
            }
        });
    }

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1 && node.matches(".testimonial-shortcode-container")) {
                    initializeCarousel(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

    document.querySelectorAll(".testimonial-shortcode-container").forEach(container => {
        initializeCarousel(container);
    });
});
        ';
        file_put_contents($js_dir . 'testimonial-carousel.js', $js_content);

        // CSS file content
        $css_content = '
.testimonial-shortcode-container {
    position: relative;
}
.testimonial-dynamic-content {
    position: relative;
    overflow: hidden;
    transition: height 0.7s ease-in-out;
}
.testimonial-item-template, .testimonial-item-template.is-active {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}
.testimonial-content-wrapper {
    position: relative;
    width: 100%;
    min-height: 200px;
}

/* Animations for sliding content */
.testimonial-content-wrapper.slide-in-from-right {
    animation: slideInFromRight 0.7s forwards;
}
.testimonial-content-wrapper.slide-in-from-left {
    animation: slideInFromLeft 0.7s forwards;
}
.testimonial-content-wrapper.slide-out-to-left {
    animation: slideOutToLeft 0.7s forwards;
}
.testimonial-content-wrapper.slide-out-to-right {
    animation: slideOutToRight 0.7s forwards;
}

@keyframes slideInFromRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideInFromLeft {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes slideOutToLeft {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(-100%); opacity: 0; }
}
@keyframes slideOutToRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

/* Style for Elementor buttons and indicators */
.prev-button, .next-button {
    cursor: pointer;
}
.elementor-testimonial-content {
    font-size:clamp(20px, 5vw, 35px);
    font-weight:regular;
    line-height: 1.3;
    color:#000000;
}
.elementor-testimonial-name {
    font-size: clamp(8px, 5vw, 24px);
    font-weight:bold;
    color:#000000;
}
.carousel-indicators {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    z-index: 10;
    margin-top: 20px;
}
.indicator {
    width: 10px;
    height: 10px;
    background-color: #FFFFFF;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s;
}
.indicator.active {
    background-color: #ed1b2f;
}
.video-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    overflow: auto;
}
.video-modal-content {
    position: relative;
    margin: 10% auto;
    padding: 20px;
    width: 90%;
    max-width: 800px;
}
.close-button {
    position: absolute;
    top: 0;
    right: 10px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10000;
}
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    overflow: hidden;
}
.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
        ';
        file_put_contents($css_dir . 'testimonial-style.css', $css_content);
    }
    add_action( 'after_setup_theme', 'create_testimonial_assets_files' );
}
