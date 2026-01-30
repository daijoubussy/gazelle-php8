/**
 * Gazelle Frontend App Component
 *
 * Main application component with routing
 */

import React, { Suspense, lazy } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';

import AppLayout from '@layouts/AppLayout';
import { useAuth } from '@hooks/useAuth';

// Lazy-loaded page components
const HomePage = lazy(() => import('@pages/Home'));
const TorrentsPage = lazy(() => import('@pages/Torrents'));
const TorrentDetailsPage = lazy(() => import('@pages/TorrentDetails'));
const ForumsPage = lazy(() => import('@pages/Forums'));
const UserProfilePage = lazy(() => import('@pages/UserProfile'));
const UploadPage = lazy(() => import('@pages/Upload'));
const RequestsPage = lazy(() => import('@pages/Requests'));
const CollagesPage = lazy(() => import('@pages/Collages'));
const TopPage = lazy(() => import('@pages/Top'));
const RulesPage = lazy(() => import('@pages/Rules'));
const WikiPage = lazy(() => import('@pages/Wiki'));
const InboxPage = lazy(() => import('@pages/Inbox'));
const SettingsPage = lazy(() => import('@pages/Settings'));
const LoginPage = lazy(() => import('@pages/Login'));
const NotFoundPage = lazy(() => import('@pages/NotFound'));

// Loading component
const LoadingContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 200px;
  color: ${variables.textGray};
`;

const Loading: React.FC = () => (
  <LoadingContainer>Loading...</LoadingContainer>
);

// Protected route wrapper
interface ProtectedRouteProps {
  children: React.ReactNode;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children }) => {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <Loading />;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <>{children}</>;
};

/**
 * Main App Component
 */
const App: React.FC = () => {
  return (
    <Suspense fallback={<Loading />}>
      <Routes>
        {/* Public routes */}
        <Route path="/login" element={<LoginPage />} />

        {/* Protected routes with layout */}
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <AppLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<HomePage />} />
          <Route path="torrents" element={<TorrentsPage />} />
          <Route path="torrents/:id" element={<TorrentDetailsPage />} />
          <Route path="forums" element={<ForumsPage />} />
          <Route path="forums/:forumId" element={<ForumsPage />} />
          <Route path="forums/:forumId/topic/:topicId" element={<ForumsPage />} />
          <Route path="user/:userId" element={<UserProfilePage />} />
          <Route path="upload" element={<UploadPage />} />
          <Route path="requests" element={<RequestsPage />} />
          <Route path="requests/:requestId" element={<RequestsPage />} />
          <Route path="collages" element={<CollagesPage />} />
          <Route path="collages/:collageId" element={<CollagesPage />} />
          <Route path="top/:category" element={<TopPage />} />
          <Route path="rules" element={<RulesPage />} />
          <Route path="wiki" element={<WikiPage />} />
          <Route path="wiki/:articleId" element={<WikiPage />} />
          <Route path="inbox" element={<InboxPage />} />
          <Route path="inbox/:conversationId" element={<InboxPage />} />
          <Route path="settings" element={<SettingsPage />} />
          <Route path="settings/:section" element={<SettingsPage />} />
        </Route>

        {/* 404 */}
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </Suspense>
  );
};

export default App;
