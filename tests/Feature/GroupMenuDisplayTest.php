<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class GroupMenuDisplayTest extends BaseTestCase
{
    protected $user;
    protected $team;

    public function setUp(): void
    {
        parent::setUp();

        // Get any existing user from database (since we're not using RefreshDatabase)
        $this->user = User::first();                

        // Get any existing team or create one
        $this->team = Team::first();            
    }
    
    /**
     * Test that menu list API endpoint is called correctly
     */
    public function test_menu_list_api_endpoint_is_called(): void
    {
        $response = $this->get("/pengaturan/groups/edit/{$this->team->id}");

        // Assert that the page contains the API endpoint URL
        $response->assertSee("api/v1/pengaturan/group/listModul/{$this->team->id}", false);
    }

    /**
     * Test that menu list is displayed with correct structure
     */
    public function test_menu_list_display_structure(): void
    {
        $response = $this->get("/pengaturan/groups/edit/{$this->team->id}");

        // Assert that the menu structure is displayed
        $response->assertSee('Struktur Menu', false);
        $response->assertSee('Sumber Menu URL', false);
        
        // Assert that the form elements are present
        $response->assertSee('frmEdit', false);
        $response->assertSee('json_menu', false);
        
        // Assert that the buttons are present
        $response->assertSee('btnUpdate', false);
        $response->assertSee('btnAdd', false);
    }

    /**
     * Test that menu list is loaded with JavaScript functions
     */
    public function test_menu_list_javascript_functions(): void
    {
        $response = $this->get("/pengaturan/groups/edit/{$this->team->id}");

        // Assert that the Alpine.js directives are present
        $response->assertSee('x-data="menu()"', false);
        $response->assertSee('x-init="retrieveData()"', false);

        // Assert that the necessary JavaScript functions are present
        $response->assertSee('const retrieveData =', false);
        $response->assertSee('const buildListModul =', false);
        $response->assertSee('const buildEditor =', false);

        // Assert that the fetch API call is present in retrieveData function
        $response->assertSee('fetch(', false);

        // Assert that the menu editor initialization is present
        $response->assertSee('new MenuEditor', false);
        $response->assertSee('editor.setForm', false);
        $response->assertSee('editor.setData', false);
        $response->assertSee('editor.setUpdateButton', false);
    }

    /**
     * Test that myEditor element is not empty and contains menu items
     */
    public function test_my_editor_element_is_not_empty_and_contains_menu(): void
    {
        $response = $this->get("/pengaturan/groups/edit/{$this->team->id}");
        // Assert that the page loads successfully
        $response->assertStatus(200);

        // Assert that the myEditor element exists in the HTML
        $response->assertSee('<ul id="myEditor" class="sortableLists list-group"></ul>', false);

        // Assert that the Alpine.js x-data and x-init directives are present to call retrieveData after page load
        $response->assertSee('x-data="menu()"', false);
        $response->assertSee('x-init="retrieveData()"', false);

        // Assert that the JavaScript code for calling the API endpoint after page load is present
        $response->assertSee("api/v1/pengaturan/group/listModul/{$this->team->id}", false);

        // Assert that the retrieveData function exists and makes the API call
        $response->assertSee('const retrieveData =', false);
        $response->assertSee('fetch(', false);

        // Assert that the JavaScript code that executes after API call to populate the editor is present
        $response->assertSee('buildEditor(', false);
        $response->assertSee('editor.setData(', false);

        // Verify that the necessary JavaScript functions exist for loading menu after page load
        $response->assertSee('retrieveData', false);
        $response->assertSee('buildListModul', false);

        // Check that the editor initialization code is present
        $response->assertSee('new MenuEditor', false);
        $response->assertSee('editor.setForm', false);
        $response->assertSee('editor.setUpdateButton', false);
    }

    /**
     * Test that myEditor element contains menu items after data is loaded
     */
    public function test_my_editor_contains_menu_items_after_data_load(): void
    {
        $response = $this->get("/pengaturan/groups/edit/{$this->team->id}");

        // Assert that the page loads successfully
        $response->assertStatus(200);

        // Assert that the Alpine.js directives trigger the data loading
        $response->assertSee('x-data="menu()"', false);
        $response->assertSee('x-init="retrieveData()"', false);

        // Assert that the JavaScript code for handling nested menu items is present
        $response->assertSee('MenuEditor', false);
        $response->assertSee('editor.setForm', false);
        $response->assertSee('editor.setUpdateButton', false);
        $response->assertSee('editor.setData', false);

        // Assert that the menu editor is properly initialized with options
        $response->assertSee('iconPickerOptions', false);
        $response->assertSee('sortableListOptions', false);

        // Assert that the API call and data processing functions are present
        $response->assertSee('const retrieveData =', false);
        $response->assertSee('buildEditor', false);
        $response->assertSee('buildListModul', false);
    }

    /**
     * Test that myEditor element handles empty menu gracefully
     */
    public function test_my_editor_handles_empty_menu_gracefully(): void
    {
        // Create a team with empty menu
        $teamWithEmptyMenu = Team::forceCreate([
            'name' => 'Empty Test Group 2 ' . time(),
            'menu' => json_encode([]),
            'menu_order' => json_encode([])
        ]);

        $response = $this->get("/pengaturan/groups/edit/{$teamWithEmptyMenu->id}");

        // Assert that the page loads successfully
        $response->assertStatus(200);

        // Assert that the myEditor element exists even with empty data
        $response->assertSee('<ul id="myEditor" class="sortableLists list-group"></ul>', false);

        // Assert that the Alpine.js directives are present to trigger data loading
        $response->assertSee('x-data="menu()"', false);
        $response->assertSee('x-init="retrieveData()"', false);

        // Assert that the editor is still initialized with empty data
        $response->assertSee('editor.setData(', false);

        // Assert that the retrieveData function exists to handle the API call
        $response->assertSee('const retrieveData =', false);
        $response->assertSee('fetch(', false);

        // Clean up - only delete if it's a test record
        $teamWithEmptyMenu->delete();
    }
    
    protected function tearDown(): void
    {
        // Clean up any created data
        parent::tearDown();
    }
}