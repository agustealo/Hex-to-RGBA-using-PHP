# Dynamic CSS: Converting Hex to RGBA using PHP

In this tutorial I will guide you through the script, step-by-step on creating a dynamic CSS setup where you can convert hex color codes to RGBA using PHP. This approach allows you to manage colors in your theme more efficiently, especially when you need different opacity levels of the same color.

## Table of Contents

1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Setup](#setup)
4. [Creating the PHP Function](#creating-the-php-function)
5. [Using the Function in CSS](#using-the-function-in-css)
6. [Example Use Case](#example-use-case)
7. [Conclusion](#conclusion)

## Introduction

In web development, managing colors dynamically can save time and ensure consistency. By converting hex colors to RGBA using PHP, you can create reusable color variables that can easily adapt to different opacity levels. This is particularly useful for theming and styling in WordPress.

## Prerequisites

- Basic knowledge of PHP and CSS.
- A working WordPress theme where you can add custom PHP and CSS.

## Setup

1. **Create a New PHP File**: If you don't already have a functions.php file in your WordPress theme, create one. This file will contain our PHP function for converting hex to RGB.

2. **Create CSS Files**: Ensure you have a style.css file for basic styles and a theme.css file for theme-specific styles.

## Creating the PHP Function

Let's start by creating a PHP function that converts a hex color code to RGB. This function will be used to generate RGBA colors dynamically.

```php
<?php
function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return implode(", ", [$r, $g, $b]);
}
?>
```

This function takes a hex color code as input and returns a string with the RGB values.

## Using the Function in CSS

Now, we'll use this function to create CSS variables in your WordPress theme. These variables will be used to style your theme dynamically.

### Step 1: Define Your PHP Variables

In your functions.php file, define the color variables you want to use:

```php
<?php
$primary_color = "#3498db";
$secondary_color = "#2ecc71";
$border_color = "#e74c3c";
?>
```

### Step 2: Generate CSS Variables

Use the hex2rgb function to convert these hex colors to RGB and define the CSS variables:

```php
<style>
:root {
    --primary-color: <?php echo $primary_color; ?>;
    --secondary-color: <?php echo $secondary_color; ?>;
    --border-color: <?php echo $border_color; ?>;
    
    /* RGB Colors */
    --primary-color-rgb: <?php echo hex2rgb($primary_color); ?>;
    --secondary-color-rgb: <?php echo hex2rgb($secondary_color); ?>;
    --border-color-rgb: <?php echo hex2rgb($border_color); ?>;
    
    /* Transparent Versions */
    --primary-color-transparent: rgba(var(--primary-color-rgb), 0.1); /* 10% opacity */
    --secondary-color-transparent: rgba(var(--secondary-color-rgb), 0.1); /* 10% opacity */
    --border-color-transparent: rgba(var(--border-color-rgb), 0.1); /* 10% opacity */
}
</style>
```

### Step 3: Use CSS Variables in Your Styles

In your CSS files (style.css and theme.css), use these variables to style your elements:

```css
body {
    font-family: var(--primary-font);
    color: var(--primary-color);
}

.search-form {
    background-color: var(--border-color-transparent);
}

.button {
    background-color: var(--primary-color);
    color: var(--secondary-color);
}

.button:hover {
    background-color: var(--primary-color-transparent);
}
```

## Example Use Case

Let's create a simple search form and button styled with these dynamic colors.

### HTML

Add the following HTML to your WordPress theme file:

```html
<form class="search-form" action="/" method="get">
    <input class="search-field" type="text" name="s" placeholder="Search...">
    <button class="wp-block-search__button" type="submit">Search</button>
</form>

<button class="button">Click Me</button>
```

### CSS

Use the CSS defined earlier to style the form and button:

```css
.search-form {
    display: flex;
    padding: 8px;
    border: 2px solid var(--border-color);
    border-radius: 4px;
    background-color: var(--border-color-transparent);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease-in-out;
}

.search-field {
    flex-grow: 1;
    margin-right: 8px;
    padding: 10px;
    border: none;
    border-radius: 4px 0 0 4px;
    font-size: 1rem;
    outline: none;
    background-color: #fff;
    color: #333;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

.search-field::placeholder {
    color: #999;
}

.search-field:focus {
    background-color: #fff;
    color: #333;
    box-shadow: 0 0 0 2px var(--link-color);
}

.wp-block-search__inside-wrapper .wp-block-search__button {
    padding: 10px 20px;
    border: none;
    border-radius: 0 4px 4px 0;
    background-color: var(--button-bg-color);
    color: var(--button-text-color);
    cursor: pointer;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

.wp-block-search__inside-wrapper .wp-block-search__button:hover {
    background-color: var(--button-hover-color);
    color: #fff;
}
```

### PHP

Ensure the PHP to generate the styles is included in your theme's header.php or a similar file:

```php
<?php
$primary_color = "#3498db";
$secondary_color = "#2ecc71";
$border_color = "#e74c3c";
?>

<style>
:root {
    --primary-color: <?php echo $primary_color; ?>;
    --secondary-color: <?php echo $secondary_color; ?>;
    --border-color: <?php echo $border_color; ?>;
    
    /* RGB Colors */
    --primary-color-rgb: <?php echo hex2rgb($primary_color); ?>;
    --secondary-color-rgb: <?php echo hex2rgb($secondary_color); ?>;
    --border-color-rgb: <?php echo hex2rgb($border_color); ?>;
    
    /* Transparent Versions */
    --primary-color-transparent: rgba(var(--primary-color-rgb), 0.1); /* 10% opacity */
    --secondary-color-transparent: rgba(var(--secondary-color-rgb), 0.1); /* 10% opacity */
    --border-color-transparent: rgba(var(--border-color-rgb), 0.1); /* 10% opacity */
}
</style>
```

## Conclusion

By using PHP to convert hex colors to RGB and creating dynamic CSS variables, you can efficiently manage and use colors in your WordPress theme. This approach helps maintain consistency, simplifies color management, and allows for easy adjustments to transparency and other properties.

You can extend this approach with more colors and variables to suit your theme's needs. This method ensures your CSS remains DRY (Don't Repeat Yourself) and maintainable.

@gustealo https://agustealo.com/
