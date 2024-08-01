/**
 * Entry for plugin sidebar.
 */

import { registerPlugin } from '@wordpress/plugins';
import Sidebar from './sidebar';

registerPlugin('wp-modified-date-control', {
  render: Sidebar,
});
