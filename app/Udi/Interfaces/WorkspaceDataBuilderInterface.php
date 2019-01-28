<?php


namespace App\Udi\Interfaces;

use Illuminate\Http\Request;

interface WorkspaceDataBuilderInterface
{
    public function withListItems($onlyItems = false): WorkspaceDataBuilderInterface;
    public function withListSort($field, $order = null): WorkspaceDataBuilderInterface;
    public function withListPagination($page, $perPage = null): WorkspaceDataBuilderInterface;
    public function withListFilter($query): WorkspaceDataBuilderInterface;
    public function withLink($link, $workspacePair = null): WorkspaceDataBuilderInterface;
    public function withFormItem($id): WorkspaceDataBuilderInterface;
    public function withParentFormItem(array $workspacePair) : WorkspaceDataBuilderInterface;
    public function withFormErrors(array $errors): WorkspaceDataBuilderInterface;
    public function withRequest(Request $request, $realId = null): WorkspaceDataBuilderInterface;

    public function build();
}
