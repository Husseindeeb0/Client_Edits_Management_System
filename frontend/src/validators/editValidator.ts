import type { EditPriority, EditStatus } from '../types/edit';

/** The edit form's fields, as typed in the inputs. */
export interface EditFormValues {
  /** The selected project's id, '' when none is chosen. */
  project_id: string;
  title: string;
  description: string;
  priority: EditPriority;
  status: EditStatus;
  /** '' when no price is set. */
  price: string;
  is_paid: boolean;
  due_date: string;
}

const MAX_PRICE = 99999999.99;

/** Same rules as the backend's StoreEditRequest; returns a message per invalid field. */
export function validateEdit(values: EditFormValues): Record<string, string> {
  const errors: Record<string, string> = {};
  const title = values.title.trim();

  if (!values.project_id) {
    errors.project_id = 'Choose a project.';
  }

  if (!title) {
    errors.title = 'The title is required.';
  } else if (title.length > 255) {
    errors.title = 'The title may not be longer than 255 characters.';
  }

  if (values.price !== '') {
    const price = Number(values.price);

    if (Number.isNaN(price) || price < 0) {
      errors.price = 'The price must be a positive number.';
    } else if (price > MAX_PRICE) {
      errors.price = 'The price is too large.';
    }
  }

  return errors;
}
