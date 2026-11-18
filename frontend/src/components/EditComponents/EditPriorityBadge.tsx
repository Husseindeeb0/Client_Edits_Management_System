import type { EditPriority } from '../../types/edit';
import Badge from '../SharedComponents/Badge';

const PRIORITY_TONES = {
  High: 'red',
  Medium: 'yellow',
  Low: 'gray',
} as const;

export default function EditPriorityBadge({ priority }: { priority: EditPriority }) {
  return <Badge label={priority} tone={PRIORITY_TONES[priority]} />;
}
