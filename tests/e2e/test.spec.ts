import { Page } from '@playwright/test';
import { test, expect, Admin } from '@wordpress/e2e-test-utils-playwright';

const createPost = async (admin: Admin) => {
  await admin.createNewPost({
    title: 'Example Post',
    content: 'This is an example post.',
  });

  await admin.editor.setPreferences('core/edit-post', {
    welcomeGuide: false,
    fullscreenMode: false,
  });
};

const savePost = async (page: Page) => {
  const editorTopBar = page.getByRole('region', {
		name: 'Editor top bar',
	});

	// If we have changes in a single entity which can be published the label is `Publish`.
	const saveButton = editorTopBar.getByRole('button', {
		name: 'Save',
		exact: true,
	});

  await saveButton.click();
}

const closePublishPanel = async (page: Page) => {
  const publishPanel = await page.locator('.editor-post-publish-panel');

  if (await publishPanel.isVisible()) {
    await publishPanel.getByRole('button', { name: 'Close panel' }).click();
  }
};

test('allows updates to modified by default', async ({ admin, page }) => {
  await createPost(admin);

  await expect(page.getByText('Allow Updates to Modified')).toBeVisible();

  const allowUpdatesPanel = await page.getByTestId('wp-modified-date-control-allow-updates-panel');

  await expect(allowUpdatesPanel).toBeVisible();
  await expect(allowUpdatesPanel.getByRole('checkbox')).toBeChecked();

  const button = await page.getByTestId('wp-modified-date-control-set-date-button');
  await expect(button).toBeVisible();
  await expect(button).toBeDisabled();
});

test('modified date should not be set by default', async ({ admin, page }) => {
  await createPost(admin);

  const modifiedDatePanel = await page.getByTestId('wp-modified-date-modify-control');

  await expect(modifiedDatePanel).toBeVisible();
  await expect(modifiedDatePanel.getByText('Not set.')).toBeVisible();
});

test('allow updates checkbox is disabled until post is published', async ({ admin, editor, page }) => {
  await createPost(admin);

  const allowUpdatesPanel = await page.getByTestId('wp-modified-date-control-allow-updates-panel');

  await expect(allowUpdatesPanel).toBeVisible();
  await expect(allowUpdatesPanel.getByRole('checkbox')).toBeDisabled();

  await editor.publishPost();
  await closePublishPanel(page);

  // The checkbox should now be enabled.
  await expect(allowUpdatesPanel.getByRole('checkbox')).toBeEnabled();
  await expect(allowUpdatesPanel.getByRole('checkbox')).toBeInViewport();
});

test('can set the modified date after publishing the post', async ({ admin, editor, page }) => {
  await createPost(admin);

  await editor.publishPost();
  await closePublishPanel(page);

  const allowUpdatesPanel = await page.getByTestId('wp-modified-date-control-allow-updates-panel');
  const button = await page.getByTestId('wp-modified-date-control-set-date-button');

  await expect(button).toBeDisabled();

  // Uncheck the checkbox to block updates and allow the modified date to be manually set.
  await allowUpdatesPanel.getByRole('checkbox').uncheck();
  await expect(button).toBeEnabled();

  await button.click();

  await expect(page.getByText('Set Modified Date')).toBeVisible();
  const panel = await page.getByTestId('wp-modified-date-control-date-popover');

  await expect(panel).toBeVisible();

  // Now that the panel is visible we will manually set the modified date.
  // Testing the date picker panel in Playwright is difficult.
  await button.click();
  await expect(panel).not.toBeVisible();

  await page.evaluate(() => {
    // @ts-ignore
    wp.data.dispatch('core/editor').editPost({ modified: '2025-08-04 12:23:53' });
  });

  await expect(page.getByText('August 4, 2025 12:23 pm')).toBeVisible();

  // Save the post and verify the date persisted.
  await savePost(page);
  await expect(page.getByText('August 4, 2025 12:23 pm')).toBeVisible();

  // Reload the page and be sure it persisted.
  await page.reload();

  await expect(page.getByText('August 4, 2025 12:23 pm')).toBeVisible();
});

test('can allow updates to a previously manually controlled post', async ({ admin, editor, page }) => {
  await createPost(admin);

  await editor.publishPost();
  await closePublishPanel(page);

  const allowUpdatesPanel = await page.getByTestId('wp-modified-date-control-allow-updates-panel');
  const button = await page.getByTestId('wp-modified-date-control-set-date-button');

  await page.evaluate(() => {
    // @ts-ignore
    wp.data.dispatch('core/editor').editPost({
      meta: {
        wp_modified_date_control_allow_updates: false,
      },
      modified: '2025-08-04 12:23:53',
    });
  });

  await expect(page.getByText('August 4, 2025 12:23 pm')).toBeVisible();
  await savePost(page);

  // Check the checkbox to allow updates to the post once saved.
  await allowUpdatesPanel.getByRole('checkbox').check();
  await expect(button).toBeDisabled();
  await savePost(page);

  // "August 4, 2025 12:23 pm" should not be found because the modified date
  // should be "now".
  await expect(page.getByText('August 4, 2025 12:23 pm')).not.toBeVisible();
});
