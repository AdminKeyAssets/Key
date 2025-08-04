<?php

namespace App\Modules\News\Helper;

class NewsHelper
{
    /**
     * @return array
     */
    public static function getRoutes()
    {
        return [
            'create_form_data' => route('admin.news.create_form_data'),
            'create' => route('admin.news.create_form'),
            'save' => route('admin.news.save'),
            'delete' => route('admin.news.delete'),
            'index' => route('admin.news.index'),
        ];
    }

    /**
     * @return array
     */
    public static function getLang()
    {
        return [
            // General texts
            'index' => __('News Management'),
            'create' => __('Create News'),
            'update' => __('Update News'),
            'view' => __('View News'),
            'actions' => __('Actions'),
            'delete_title' => __('Delete'),
            'update_successfully' => __('News Updated Successfully'),
            'save_successfully' => __('News Saved Successfully'),
            'delete_successfully' => __('News Deleted Successfully'),
            'save_text' => __('Save'),

            // Form inputs
            'title' => __('Title'),
            'content' => __('Content'),
            'image' => __('Image'),
            'status' => __('Status'),
            'investors' => __('Investors'),
            'manager' => __('Manager'),
            'created_at' => __('Created At'),
            'updated_at' => __('Updated At'),
            
            // Confirmation
            'confirm_save' => __('Save'),
            'confirm_save_yes' => __('Yes'),
            'confirm_save_no' => __('No'),
            
            // Status options
            'published' => __('Published'),
            'draft' => __('Draft'),
            'archived' => __('Archived'),
            
            // Select options
            'select_investors' => __('Select Investors'),
            'selected_investors' => __('Selected Investors'),
            'select_investor_placeholder' => __('Select Investors'),
            'all_investors' => __('All Investors'),
            
            // Search and filter
            'search' => __('Search'),
            'filter' => __('Filter'),
            'all_status' => __('All Status'),
        ];
    }
}
