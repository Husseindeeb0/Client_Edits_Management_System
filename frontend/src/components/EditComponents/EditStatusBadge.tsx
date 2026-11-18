import type { EditStatus } from '../../types/edit';
import Badge from '../SharedComponents/Badge';

const STATUS_TONES = {
  Pending: 'gray',
  Confirmed: 'blue',
  'In Progress': 'yellow',
  Done: 'green',
  Cancelled: 'red',
} as const;

export default function EditStatusBadge({ status }: { status: EditStatus }) {
  return <Badge label={status} tone={STATUS_TONES[status]} />;
}
