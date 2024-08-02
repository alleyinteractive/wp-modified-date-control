import {
  Button,
  DateTimePicker,
  Dropdown,
  FormToggle,
  Tooltip,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { dateI18n, getSettings } from '@wordpress/date';
import { usePostMetaValue } from '@alleyinteractive/block-editor-tools';

import { useMemo } from 'react';

import styles from './sidebar.module.scss';

/**
 * Meta key for storing the user's preference to allow updates to the modified date.
 */
const META_KEY_ALLOW_UPDATES = 'wp_modified_date_control_allow_updates';

/**
 * Sidebar to control the modified date for a post.
 */
function Sidebar() {
  // Retrieve the post's modified date from Gutenberg.
  const modifiedDate = useSelect(
    (select) => (select('core/editor') as any).getEditedPostAttribute('modified'),
    [],
  );
  const { editPost } = useDispatch('core/editor');
  const settings = getSettings();
  const [allowUpdates, setAllowUpdates] = usePostMetaValue(META_KEY_ALLOW_UPDATES);

  // Mirrors Gutenberg's logic for determining if the time format is 12-hour.
  const is12HourTime = useMemo(() => /a(?!\\)/i.test(
    settings.formats.time
      .toLowerCase() // Test only the lower case a.
      .replace(/\\\\/g, '') // Replace "//" with empty strings.
      .split('')
      .reverse()
      .join(''), // Reverse the string and test for "a" not followed by a slash.
  ), [settings]);

  return (
    <>
      <PluginPostStatusInfo>
        <div className={`editor-post-status-info ${styles.postModifiedRow}`}>
          <div className="editor-post-panel__row-label">
            {__('Modified', 'wp-modified-date-control')}
          </div>
          <div className="editor-post-panel__row-control">
            <Dropdown
              renderToggle={({ isOpen, onToggle }) => (
                <Button
                  variant="tertiary"
                  onClick={onToggle}
                  aria-expanded={isOpen}
                  disabled={allowUpdates}
                >
                  {modifiedDate
                    ? dateI18n(`${settings.formats.date} ${settings.formats.time}`, modifiedDate, undefined)
                    : __('Not set.', 'wp-modified-date-control')}
                </Button>
              )}
              renderContent={() => (
                <div className={styles.postModifiedPopover}>
                  <div className={styles.postModifiedPopoverHeader}>
                    <h3>
                      {__('Modified Date', 'wp-modified-date-control')}
                    </h3>
                    <Button
                      variant="tertiary"
                      onClick={() => editPost({ modified: undefined })}
                    >
                      {__('Now', 'wp-modified-date-control')}
                    </Button>
                  </div>
                  <DateTimePicker
                    currentDate={modifiedDate}
                    onChange={(date) => editPost({ modified: date })}
                    is12Hour={is12HourTime}
                  />
                </div>
              )}
            />
          </div>
        </div>
      </PluginPostStatusInfo>
      <PluginPostStatusInfo>
        <div className={`editor-post-status-info ${styles.postModifiedRow}`}>
          <label htmlFor="wp-modified-date-control-allow-updates-to-modified">
            {__('Allow Updates to Modified', 'wp-modified-date-control')}
          </label>
          <Tooltip text={__('When enabled, the modified date will be updated when the post is saved.', 'wp-modified-date-control')}>
            <FormToggle
              id="wp-modified-date-control-allow-updates-to-modified"
              checked={allowUpdates}
              onChange={() => setAllowUpdates(!allowUpdates)}
            />
          </Tooltip>
        </div>
      </PluginPostStatusInfo>
    </>
  );
}

export default Sidebar;
