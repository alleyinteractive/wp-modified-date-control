import { Button, DateTimePicker, Dropdown } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { dateI18n, getSettings } from '@wordpress/date';

import { useMemo } from 'react';

import styles from './sidebar.module.scss';

/**
 * Sidebar to control the modified date for a post.
 */
function Sidebar() {
  // Retrieve the post's modified date from Gutenberg.
  const modifiedDate = useSelect(
    (select) => (select('core/editor') as any).getEditedPostAttribute('modified'),
  );
  const { editPost } = useDispatch('core/editor');
  const settings = getSettings();

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
    <PluginPostStatusInfo>
      <div className={`${styles.postModifiedRow} editor-post-status-info`}>
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
              >
                {modifiedDate
                  ? dateI18n(`${settings.formats.date} ${settings.formats.time}`, modifiedDate, undefined)
                  : __('Not set.', 'wp-modified-date-control')}
              </Button>
            )}
            renderContent={() => (
              <DateTimePicker
                currentDate={modifiedDate}
                onChange={(date) => editPost({ modified: date })}
                is12Hour={is12HourTime}
              />
            )}
          />
        </div>
      </div>
    </PluginPostStatusInfo>
  );
}

export default Sidebar;
