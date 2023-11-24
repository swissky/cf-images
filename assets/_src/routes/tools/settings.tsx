/**
 * External dependencies
 */
import { useContext } from 'react';

/**
 * Internal dependencies
 */
import SettingsContext from '../../context/settings';
import ImageAI from '../../modules/image-ai';
import ImageCompress from '../../modules/image-compress';
import Login from './login';
import CompressionStats from '../../modules/ai-stats';
import ImageGenerate from '../../modules/image-generate';
import UpsellModule from '../../modules/upsell';
import FuzionDisconnect from '../../modules/actions/ai-disconnect';

/**
 * Cloudflare Images settings routes.
 *
 * @class
 */
const ToolsSettings = () => {
	const { hasFuzion, setFuzion } = useContext(SettingsContext);

	if (!hasFuzion) {
		return <Login />;
	}

	return (
		<div className="columns is-multiline">
			<CompressionStats />
			<ImageAI />
			<ImageCompress />
			<ImageGenerate />
			<UpsellModule />
			<FuzionDisconnect />
		</div>
	);
};

export default ToolsSettings;
