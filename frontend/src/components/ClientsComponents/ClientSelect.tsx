import { useState } from 'react';
import { useGetClientDetailsQuery, useGetInfiniteClientsInfiniteQuery } from '../../client/clientApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface ClientSelectProps {
  id: string;
  value: number | null;
  onChange: (clientId: number) => void;
}

/** Choose a client: searchable, more clients load while scrolling. */
export default function ClientSelect({ id, value, onChange }: ClientSelectProps) {
  const [search, setSearch] = useState('');
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching, isFetchingNextPage, hasNextPage, fetchNextPage } = useGetInfiniteClientsInfiniteQuery(search);
  // The current client may not be loaded yet, so get its name on its own (when editing)
  const { data: current } = useGetClientDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a client"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.pages ?? []).flatMap((page) =>
        page.data.map((client) => ({ id: client.id, label: client.name, hint: client.phone_number })),
      )}
      isLoading={isFetching && !isFetchingNextPage}
      isLoadingMore={isFetchingNextPage}
      hasMore={hasNextPage}
      onLoadMore={fetchNextPage}
      onSearch={setSearch}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
