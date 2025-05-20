import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/pages/team/empty_members_view.dart';
import 'package:flutter_mobile_app/pages/team/team_header.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/widgets/member_card.dart';

class TeamPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const TeamPage({super.key, required this.userData});

  @override
  State<TeamPage> createState() => _TeamPageState();
}

class _TeamPageState extends State<TeamPage> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String _errorMessage = '';
  Map<String, dynamic> _teamInfo = {};
  List<dynamic> _teamMembers = [];

  @override
  void initState() {
    super.initState();
    _loadTeamData();
  }

  Future<void> _loadTeamData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      // Get user's team ID from userData
      final teamIdRaw = widget.userData['user']['team_id'];

      if (teamIdRaw == null) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'You are not assigned to any team';
        });
        return;
      }

      // Convert team Id to integer
      final teamId = teamIdRaw is String ? int.parse(teamIdRaw) : teamIdRaw;

      // Fetch team info
      final teamResponse = await _apiService.getTeamInfo(teamId);

      if (teamResponse['status']) {
        setState(() {
          _teamInfo = teamResponse['data'] ?? {};
        });

        // Fetch team members
        final membersResponse = await _apiService.getTeamMembers(teamId);

        if (membersResponse['status']) {
          setState(() {
            _teamMembers = membersResponse['data']['members'] ?? [];
            _isLoading = false;
          });
        } else {
          setState(() {
            _errorMessage =
                membersResponse['msg'] ?? 'Failed to load team members';
            _isLoading = false;
          });
        }
      } else {
        setState(() {
          _errorMessage =
              teamResponse['msg'] ?? 'Failed to load team information';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error loading team data: ${e.toString()}';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _loadTeamData,
      child:
          _isLoading
              ? Center(child: CircularProgressIndicator())
              : _errorMessage.isNotEmpty
              ? _buildErrorView()
              : _buildTeamView(),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, size: 70, color: AppTheme.errorColor),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'Team Information Unavailable',
              style: AppTheme.titleStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              _errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(onPressed: _loadTeamData, child: Text('Try Again')),
          ],
        ),
      ),
    );
  }

  Widget _buildTeamView() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Team Header
            TeamHeader(
              name: _teamInfo['name'] ?? 'Team',
              description:
                  _teamInfo['description'] ?? 'No description available',
            ),
            SizedBox(height: AppTheme.spacingLg),

            // Team Members Section
            Text('Team Members', style: AppTheme.titleStyle),
            SizedBox(height: AppTheme.spacingMd),

            // Team Members List
            _teamMembers.isEmpty
                ? EmptyMembersView()
                : ListView.builder(
                  physics: NeverScrollableScrollPhysics(),
                  shrinkWrap: true,
                  itemCount: _teamMembers.length,
                  itemBuilder: (context, index) {
                    final member = _teamMembers[index];
                    return MemberCard(
                      name: member['name'] ?? 'Unknown',
                      email: member['email'] ?? '',
                      role: member['role'] ?? 'Member',
                      isCurrentUser:
                          member['id'].toString() ==
                          widget.userData['user']['id'].toString(),
                    );
                  },
                ),
          ],
        ),
      ),
    );
  }
}
