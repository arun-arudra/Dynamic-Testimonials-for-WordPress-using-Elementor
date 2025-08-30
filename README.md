# Elementor Dynamic Testimonial Carousel

A custom WordPress solution that creates a dynamic testimonial carousel powered by a custom post type and a reusable Elementor template. This setup ensures that your testimonials inherit all of Elementor's styling and are fully responsive.

---

### Key Features

* **Custom Post Type:** Easily manage and organize your testimonials from the WordPress dashboard.
* **Elementor Integration:** Design the layout for one testimonial and automatically apply it to all.
* **Responsive Typography:** Uses CSS `clamp()` for fluid, responsive font sizes.
* **Video Testimonials:** Supports YouTube video embeds with a dynamic modal popup.
* **Smooth Animations:** Includes subtle sliding animations for a polished user experience.

---

### Installation and Usage

#### Step 1: Add the Code to Your Theme

Copy and paste the entire PHP code block into your theme's `functions.php` file. This code handles the creation of the custom post type, the shortcode, and automatically enqueues the necessary CSS and JavaScript files.

#### Step 2: Create Your Testimonials

1.  In your WordPress dashboard, navigate to **Testimonials**.
2.  Click **Add New**.
3.  Enter the client's name as the **title**.
4.  Add the testimonial content in the main editor.
5.  Set a **Featured Image** for the client's photo.
6.  Use the **Testimonial Details** section to add a product name and a YouTube video URL (optional).
7.  Publish the testimonial.

Repeat this process for all your testimonials.

#### Step 3: Use the Elementor Template

You have two options for setting up the Elementor template: you can use the pre-built template provided in this repository or create your own from scratch.

**Option A: Import the Pre-Built Template**
1.  Download the `testimonial-item-template.json` file from the repository's root directory.
2.  In your WordPress dashboard, go to **Templates > Saved Templates**.
3.  Click the **Import Templates** button (cloud icon) and upload the file.

**Option B: Build Your Own Template**
1.  In your WordPress dashboard, go to **Templates > Saved Templates**.
2.  Click **Add New** and choose **Section** as the template type. Give it a name like "Testimonial Item Template."
3.  Design the layout for **one single testimonial item**. You must add the following Elementor widgets and give them the specified CSS classes in the **Advanced** settings:
    * **Main Container:** Select the main container or section that holds all your widgets. In the **Advanced** tab, add the CSS class: `testimonial-content-wrapper`
    * **Image Container:** If you have an image, add a container or section for it and give it the CSS class: `testimonial-image-container`
    * **Play Button:** If you want a video play button, add a Button widget inside your image container and give it the CSS class: `play-button`
    * **Testimonial Content:** Add a Text Editor widget for the main testimonial text and give it the CSS class: `elementor-testimonial-content`
    * **Client Name:** Add a Heading widget for the client's name and give it the CSS class: `elementor-testimonial-name`
    * **Other Text:** Add another Heading or Text Editor widget for any other text (like the product name) and give it the CSS class: `elementor-testimonial-product`  
4.  To add navigation, place buttons on your page and assign the following CSS classes in their **Advanced** settings:
    * **Previous Button:** Give it the class `prev-button`
    * **Next Button:** Give it the class `next-button`

5.  After building the template, click **Publish**. Note the **Template ID** from the URL in your browser's address bar (e.g., if the URL is `.../wp-admin/post.php?post=25&action=elementor`, your ID is `25`).

#### Step 4: Use the Shortcode on a Page

1.  Open a new or existing page with Elementor.
2.  Add a **Shortcode** widget.
3.  Enter the following shortcode, replacing `YOUR_TEMPLATE_ID` with the ID of the template you imported or created:
    ```
    [testimonials template_id="YOUR_TEMPLATE_ID"]
    ```
4.  To add navigation, place buttons on your page and assign the following CSS classes in their **Advanced** settings:
    * **Previous Button:** Give it the class `prev-button`
    * **Next Button:** Give it the class `next-button`
5.  To change the indicator colour, edit the `function.php` file located in your theme's  directory and modify the colour value for the `.indicator.active` class.

6.  Publish the page. Your dynamic testimonial carousel will now be live!
