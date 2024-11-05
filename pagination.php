<?php
/**
 * Function to generate pagination links.
 *
 * @param int $totalPages The total number of pages.
 * @param int $page The current page.
 * @param int $categoryID The category ID for the pagination links.
 * @return string Returns HTML code for pagination links.
 */
function generatePaginationLinks($totalPages, $page, $categoryID) {
    $paginationHTML = '<div class="pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        // Add the category_id parameter to the link
        $paginationHTML .= '<a href="?category_id=' . $categoryID . '&page=' . $i . '" class="' . $activeClass . '">' . $i . '</a>';
    }
    $paginationHTML .= '</div>';
    return $paginationHTML;
}
?>
